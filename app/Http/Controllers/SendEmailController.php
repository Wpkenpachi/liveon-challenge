<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\ClientEmail;
use Mailgun\Mailgun;
use Mailgun\HttpClient\HttpClientConfigurator;
use Mailgun\Hydrator\NoopHydrator;

class SendEmailController extends Controller
{
    protected $mg;
    protected $domain;

    public function __construct() {
        $configurator = new HttpClientConfigurator();
        $configurator->setApiKey(env('MAILGUN_SECRET'));
        $this->mg = new Mailgun($configurator, new NoopHydrator());
        $exploded_domain = explode('/', env('MAILGUN_DOMAIN'));
        $this->domain = $exploded_domain[ count($exploded_domain)-1 ];
    }

    private function get_begin_time() { // return 1 hour ahead as epoch time
        $datetime = date("Y-m-d H:i:s");
        $timestamp = strtotime($datetime);
        $time = $timestamp + (1 * 60 * 60);
        return strtotime(date("Y-m-d H:i:s", $time));
    }

    private function getDeliveredOrFailedEmails($json, $receivers) {
        $delivered  = [];
        $failed     = [];
        foreach ($json as $arr) {
            foreach ($arr as $item) {
                if ($item["event"] == "delivered") {
                    $delivered[] = $item["envelope"]["targets"];
                } else if (in_array($item["event"], ["rejected", "failed"])) {
                    $failed[] = $item["envelope"]["targets"];
                }
            }
        }

        // Getting not found emails on event
        foreach (array_diff($receivers, $delivered) as $fail) {
            array_push($failed, $fail);
        }

        return [
            'delivered' => array_unique($delivered),
            'failed'    => array_unique($failed)
        ];
    }

    private function get_email_status($receivers) {
        try {
            $event = [];
            foreach ($receivers as $receiver) {
                $queryString = array(
                    'begin'        => $this->get_begin_time(),
                    'limit'        => 3,
                    'ascending'    => 'no',
                    'pretty'       => 'yes',
                    'recipient'    => $receiver
                );
                $response = $this->mg->events()->get($this->domain, $queryString);
                $response_as_array = json_decode($response->getBody()->getContents(), true);
                $event[] = $response_as_array["items"];
            }
            if (!count($event)) throw new \Error('Missing event');
            $payload = (array) $this->getDeliveredOrFailedEmails($event, $receivers);
            return $payload;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function custome_email(Request $request) {
        $validator = Validator::make($request->all(), [
            'from' => 'required|string',
            'subject' => 'required|string',
            'to' => 'required|array',
            'html' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        try {
            // Sending Email for Multiple Receivers
            $receivers = $request->get('to');
            foreach ($receivers as $receiver) {
                $response = $this->mg->messages()->send($this->domain, [
                    'from'    => $request->get('from'),
                    'to'      => $receiver,
                    'subject' => $request->get('subject'),
                    'html'    => $request->get('html')
                ]);
            }

            // Getting
            $payload = $this->get_email_status((array) $receivers);
            return response()->json($payload);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()]);
        }
    }
}
