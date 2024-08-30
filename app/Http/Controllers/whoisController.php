<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\WhoisService;
use DateTime;
use Illuminate\Http\Request;

class whoisController extends Controller
{
    protected $whoisService;

    public function __construct(WhoisService $whoisService)
    {
        $this->whoisService = $whoisService;
    }

    public function lookup(Request $request)
    {
        if (!$request->has('domain')) {
            return response()->json(['error' => 'Missing Parameters']);
        }
        $domain = $request->input('domain');
        $type = $request->input('type', 'domain');

        $whoisData = $this->whoisService->lookupDomain($domain);
        if ($whoisData) {
            if (is_array($whoisData)) { //format it to JSON
                $whoisData = json_decode(json_encode($whoisData));
            }

            //check for error
            if (!empty($whoisData->WhoisRecord->dataError)) {
                return response()->json(['error' => $whoisData->WhoisRecord->dataError]);
            } elseif (!empty($whoisData->ErrorMessage)) {
                return response()->json(['error' => $whoisData->ErrorMessage->msg]);
            } else {
                if ($type === 'domain') {
                    $registrationDate = new DateTime($whoisData->WhoisRecord->createdDate);
                    $currentDate = new DateTime();
                    $interval = $registrationDate->diff($currentDate);
                    $hostNames = implode(',',$whoisData->WhoisRecord->nameServers->hostNames);
                    $returnData = [
                        'Domain Name' => $whoisData->WhoisRecord->domainName,
                        'Registrar Name' => $whoisData->WhoisRecord->registrarName,
                        'Registration Date' => $whoisData->WhoisRecord->createdDate,
                        'Expiration Date' => $whoisData->WhoisRecord->expiresDate,
                        'Estimated Domain Age' => $interval->y . " years, " . $interval->m . " months, " . $interval->d . " days ",
                        'Hosts' => (strlen($hostNames) > 25) ? substr($hostNames,0,25).'...' : $hostNames,
                    ];
                } else {
                    $returnData = [
                        'Registrant Name' => $whoisData->WhoisRecord->registrant->name,
                        'TechnicalContact Name' => $whoisData->WhoisRecord->technicalContact->name,
                        'Administrative Contact Name' => $whoisData->WhoisRecord->administrativeContact->name,
                        'Contact Email' => $whoisData->WhoisRecord->contactEmail,
                    ];
                }

                return response()->json($returnData);
            }
        }

        return response()->json(['error' => 'Unable to fetch data'], 500);
    }
}
