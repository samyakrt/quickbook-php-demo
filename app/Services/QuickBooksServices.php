<?php
namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use QuickBooksOnline\API\Facades\Item;
use Illuminate\Support\Facades\Session;
use QuickBooksOnline\API\Facades\Account;
use QuickBooksOnline\API\Facades\Invoice;
use QuickBooksOnline\API\Facades\Customer;
use QuickBooksOnline\API\Data\IPPIntuitEntity;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2LoginHelper;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;

class QuickBooksServices {

    public $dataService;

    public function __construct() {

        $this->dataService= DataService::Configure(array(
            'auth_mode' => 'oauth2',
            'ClientID' => config('services.quick_books.client_id'),
            'ClientSecret' =>  config('services.quick_books.client_secret'),
            'RedirectURI' => config('services.quick_books.auth_redirect_uri'),
            'scope' => config('services.quick_books.ouath_scope'),
            'baseUrl' => config('services.quick_books.mode')
        ));
    }

    public function getAuthUrl() {
        $OAuth2LoginHelper = $this->dataService->getOAuth2LoginHelper();
        $authUrl = $OAuth2LoginHelper->getAuthorizationCodeURL();

        return $authUrl;
    }

    public function getToken($query_string) {
        
        \Log::info('query_string:'.\json_encode($query_string));
        $OAuth2LoginHelper = $this->dataService->getOAuth2LoginHelper();
        
        $parseUrl = $this->parseAuthRedirectUrl($query_string);

        $accessToken = $OAuth2LoginHelper->exchangeAuthorizationCodeForToken($parseUrl['code'], $parseUrl['realmId']);
        $this->dataService->updateOAuth2Token($accessToken);

        // Session::set('accessToken',$accessToken);
        
        $data = [
            'token_type' => 'bearer',
            'access_token' => $accessToken->getAccessToken(),
            'refresh_token' => $accessToken->getRefreshToken(),
            'refresh_token_expires_in' => $accessToken->getRefreshTokenExpiresAt(),
            'expires_in' => $accessToken->getAccessTokenExpiresAt(),
            'company_id' => $parseUrl['realmId'],
        ];

     

        
        // Session::set('tokenData',(object) $data);
        session(['tokenData' => (object) $data]);

        $this->dataService->Update(new IPPIntuitEntity([
            'accessTokenKey'  => session('tokenData')->access_token,
            'refreshTokenKey' => session('tokenData')->refresh_token,
            'QBORealmID'      => session('tokenData')->company_id,
        ]));
        return redirect(route('home'));


    }

    private function parseAuthRedirectUrl($url) {

        parse_str($url,$qsArray);
        return array(
            'code' => $qsArray['code'],
            'realmId' => $qsArray['realmId']
        );
    }

    
    public function refreshToken($theRefreshTokenValue) {
        $oauth2LoginHelper  = new OAuth2LoginHelper(config('services.quick_books.client_id'),config('services.quick_books.client_secret'));
        $accessTokenObj = $oauth2LoginHelper->refreshAccessTokenWithRefreshToken($theRefreshTokenValue);
        $accessTokenValue = $accessTokenObj->getAccessToken();
        $refreshTokenValue = $accessTokenObj->getRefreshToken();
        $tokenData = [
            'token_type' => 'bearer',
            'access_token' => $accessToken->getAccessToken(),
            'refresh_token' => $accessToken->getRefreshToken(),
            'refresh_token_expires_in' => $accessToken->getRefreshTokenExpiresAt(),
            'expires_in' => $accessToken->getAccessTokenExpiresAt()
        ];

        session(['tokenData' => (object) $tokenData]);


    }

    public function createCustomer() {

        $token_expiration = Carbon::parse(session('tokenData')->expires_in);


        $dataService = DataService::configure([
            'auth_mode' => 'oauth2',
            'ClientID' => config('services.quick_books.client_id'),
            'ClientSecret' =>  config('services.quick_books.client_secret'),
            'accessTokenKey'  => session('tokenData')->access_token,
            'refreshTokenKey' => session('tokenData')->refresh_token,
            'QBORealmID'      => session('tokenData')->company_id,
            'baseUrl'         => "development"
        ]);
        $faker = \Faker\Factory::create();

        $firstName = $faker->firstName;
        $lastName = $faker->lastName;
    
        $name = $firstName . ' '.$lastName;
    
        $customeerDetail = [
            'DisplayName' => $name,
            'FamilyName' => $firstName,
            'GivenName' => $firstName,
            'MiddleName' => '',
            'PrimaryEmailAddr' => [
                "Address" => $faker->safeEmail
            ]
    
        ];
        $customer = Customer::create($customeerDetail);
    
        $resultingCustomer = $dataService->Add($customer);
        session(['customer'=> (object) $resultingCustomer]);
        $error = $dataService->getLastError();
        if ($error) {
            echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
            echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
            echo "The Response message is: " . $error->getResponseBody() . "\n";
            die();
        }

        return redirect()->back();

        
    }

    public function createAccount() {

        $dataService = DataService::configure([
            'auth_mode' => 'oauth2',
            'ClientID' => config('services.quick_books.client_id'),
            'ClientSecret' =>  config('services.quick_books.client_secret'),
            'accessTokenKey'  => session('tokenData')->access_token,
            'refreshTokenKey' => session('tokenData')->refresh_token,
            'QBORealmID'      => session('tokenData')->company_id,
            'baseUrl'         => "development"
        ]);

        $accountResource = Account::create([
            'Name' => 'Voiceoverview account final Test',
            'AccountType' => 'Income'
        ]);

        
        $accountObj = $dataService->Add($accountResource);

        $error = $dataService->getLastError();
        if ($error) {
            echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
            echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
            echo "The Response message is: " . $error->getResponseBody() . "\n";
        }
        else {
            echo "Created Id={$accountObj->Id}. Reconstructed response body:\n\n";
            $xmlBody = XmlObjectSerializer::getPostXmlFromArbitraryEntity($accountObj, $urlResource);
            echo $xmlBody . "\n";
        }
        session(['account'=>  $accountObj]);
        session(['account_id' =>  $accountObj->Id]);

        return redirect()->back();



    }
    public function createItem() {

        $dataService = DataService::configure([
            'auth_mode' => 'oauth2',
            'ClientID' => config('services.quick_books.client_id'),
            'ClientSecret' =>  config('services.quick_books.client_secret'),
            'accessTokenKey'  => session('tokenData')->access_token,
            'refreshTokenKey' => session('tokenData')->refresh_token,
            'QBORealmID'      => session('tokenData')->company_id,
            'baseUrl'         => "development"
        ]);

        $resourceObj = Item::create([
            'Name' => 'new service',
            'Type' => 'Service',
            "UnitPrice" => '200',
            "IncomeAccountRef" => [
                'value'=>session('account')->Id
            ]
        ]);

        $itemObj = $dataService->Add($resourceObj);

        $error = $dataService->getLastError();
        if ($error) {
            echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
            echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
            echo "The Response message is: " . $error->getResponseBody() . "\n";
        }
        else {
            echo "Created Id={$itemObj->Id}. Reconstructed response body:\n\n";
            $xmlBody = XmlObjectSerializer::getPostXmlFromArbitraryEntity($itemObj, $urlResource);
            echo $xmlBody . "\n";
        }
        // dd($itemObj);
        session(['item' => (object) $itemObj]);

        // return redirect()->back();
    }

    public function createInvoice() {

        // dd(session('item'));
        $dataService = DataService::configure([
            'auth_mode' => 'oauth2',
            'ClientID' => config('services.quick_books.client_id'),
            'ClientSecret' =>  config('services.quick_books.client_secret'),
            'accessTokenKey'  => session('tokenData')->access_token,
            'refreshTokenKey' => session('tokenData')->refresh_token,
            'QBORealmID'      => session('tokenData')->company_id,
            'baseUrl'         => "development"
        ]);
        
        $dataService->throwExceptionOnError(true);
        \Log::info(session('customer')->Id);
        $theResourceObj = Invoice::create([
            "Line" => [
          [
            "Amount" => 100.00,
            "DetailType" => "SalesItemLineDetail",
            "SalesItemLineDetail" => [
              "ItemRef" => [
                "value" => session('item')->Id,

               ]
             ]
             ]
           ],
       "CustomerRef"=> [
         "value"=> session('customer')->Id
       ],
             "BillEmail" => [
                   "Address" => "tuladharsamyak@gmail.com"
             ],
       ]);

     
     $resultingInvoice = $dataService->Add($theResourceObj);

     $error = $dataService->getLastError();
    if ($error) {
        echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
        echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
        echo "The Response message is: " . $error->getResponseBody() . "\n";
    }
    else {
        echo "Created Id={$resultingInvoice->Id}. Reconstructed response body:\n\n";
        // $xmlBody = XmlObjectSerializer::getPostXmlFromArbitraryEntity($resultingInvoice, $urlResource);
        // echo $xmlBody . "\n";
    }
    //  dd($resultingInvoice);     
     session(['inovice' =>(object) $resultingInvoice]);

     return redirect()->back();
    }


    public function sendInvoice() {
        
        $dataService = DataService::configure([
            'auth_mode' => 'oauth2',
            'ClientID' => config('services.quick_books.client_id'),
            'ClientSecret' =>  config('services.quick_books.client_secret'),
            'accessTokenKey'  => session('tokenData')->access_token,
            'refreshTokenKey' => session('tokenData')->refresh_token,
            'QBORealmID'      => session('tokenData')->company_id,
            'baseUrl'         => "development"
        ]);

        // dd('test');
        // dd(session('inovice'));
        $result = $dataService->SendEmail(session('inovice'));

        $error = $dataService->getLastError();
        if ($error) {
            echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
            echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
            echo "The Response message is: " . $error->getResponseBody() . "\n";
        }
        else {
              dd($result);
            // echo "Created Id={$result->Id}. Reconstructed response body:\n\n";
            // // $xmlBody = XmlObjectSerializer::getPostXmlFromArbitraryEntity($result, $urlResource);
            // echo $xmlBody . "\n";
        }
    }
}
