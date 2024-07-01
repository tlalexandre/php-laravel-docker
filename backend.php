<?php

/************************************************************
                        DISCLAIMER
THIS EXAMPLE CODE IS PROVIDED TO YOU ONLY ON AN "AS IS"
BASIS WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,
EITHER EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION
ANY WARRANTIES OR CONDITIONS OF TITLE, NON-INFRINGEMENT,
MERCHANTABILITY OR FITNESS FOR A PARTICULAR PURPOSE.
PAYPAL MAKES NO WARRANTY THAT THE SOFTWARE OR
DOCUMENTATION WILL BE ERROR-FREE. IN NO EVENT SHALL THE
COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY
DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF
USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT,
STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 ***************************************************************/


/*
    Methods in this class:
        - generateAccessToken()
        - createOrder()
        - responseParameters($order)
        - paymentSource()
        - capturePayment()
*/


// Your Credentials are stored on developer.paypal.com --> My Apps & Credentials --> Your App
$CLIENT_ID = "Acg9KCKdtGoxx6rZGPjAXpAxiA60pKvnTBcXlM3dj7aSgX4Vo5sqinrTSrSrwOZjT-UYLg873nEr_vPX";
$APP_SECRET = "EPIrgzI3uZBmwifZPHY2Shw4vJo9KSHKCv62Wbo_iFT9IqUrw_e6u61rt4QiDHz9MKgCm9FuvCGYL8tv";
global $userIDToken;
global $customerID;

class MyOrder
{
    private $accessToken;
    private $orderId;


    // Generating an Access Token for a new call to the API
    // It's good practice to generate a new access token for each call
    public function generateAccessToken()
    {
        global $CLIENT_ID;
        global $APP_SECRET;
        global $userIDToken;
        if (isset($_COOKIE['customerID'])) {
            $customerID = $_COOKIE['customerID'];
        } else {
            $customerID = "";
        }
        $curl = curl_init();

        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => "https://api.sandbox.paypal.com/v1/oauth2/token",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => "grant_type=client_credentials&response_type=id_token&target_customer_id=" . $customerID,
                CURLOPT_USERPWD => $CLIENT_ID . ":" . $APP_SECRET,
                CURLOPT_HEADER => false,
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/x-www-form-urlencoded",
                ),
            )
        );

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "<pre>cURL Error #:" . $err . "</pre>";
        } else {
            $response = json_decode($response);
            $this->accessToken = $response->access_token;
            $userIDToken = $response->id_token;
        }
    }


    // Create order
    public function createOrder()
    {
        $this->generateAccessToken();

        $data = [
            "intent" => "CAPTURE",
            "purchase_units" => [
                [
                    "amount" => [
                        "currency_code" => "EUR",
                        "value" => "240.00",
                    ],
                    "shipping" => [
                        "address" => [
                            "address_line_1" => "11,Rue St Exupery",
                            "admin_area_2" => "Vannes",
                            "admin_area_1" => "Morbihan",
                            "postal_code" => "56000",
                            "country_code" => "FR",
                        ],
                        "type" => "SHIPPING",
                    ],
                    // Please change the value with your company name
                    "soft_descriptor" => "Primark web",
                ]
            ],
        ];

        /* 
            IMPORTANT:
            In app.js you have two Create Orders, one for PayPal buttons and another for Advanced Credit Card. 
            In each Create Order there is a Fetch with a URL (endpoint to backende.php). The URL for the button defines the task=button URLS parameter, 
            while the Advanced Credit Card Create Order endpoint defines task=advancedCC URLS parameter. In this way we can distinguish whether 
            the order comes from the PayPal button or from the Advanced Credit Card, by doing so we can establish which 
            of the following arrays to join to the array created above.
            
            If you decide to make changes, make sure you always merge the correct payment_source and always check that the API 
            response returns you the correct payment_source (card or paypal) in according to the request.

            payment_source->button: More info here: https://developer.paypal.com/docs/api/orders/v2/#orders_create!path=payment_source/paypal&t=request
            payment_source->card documentation to trigger the 3DS: https://developer.paypal.com/docs/checkout/advanced/customize/3d-secure/api/

            SCA_WHEN_REQUIRED is the default when neither parameter is explicitly passed.
        */

        $paypal_button_vault = [
            "payment_source" => [
                "paypal" => [
                    // Experience Context customizes the payer experience during the approval process for payment with PayPal.
                    // E.g. shipping_preference, your company brand name, user_action, payment_method_preference, locale
                    // https://developer.paypal.com/docs/api/orders/v2/#orders_create!path=payment_source/paypal/experience_context&t=request
                    "experience_context" => [
                        // Please change brand_name's value with your company name if you want to use this property.
                        "payment_method_selected" => "PAYPAL",
                        "payment_method_preference" => "IMMEDIATE_PAYMENT_REQUIRED",
                        "brand_name" => "Primark Stores Limited",
                        "landing_page" => "LOGIN",
                        "shipping_preference" => "SET_PROVIDED_ADDRESS",
                        "user_action" => "PAY_NOW",
                        "return_url" => "https://example.com/returnUrl",
                        "cancel_url" => "https://example.com/cancelUrl",
                    ],
                    "attributes" => [
                        "vault" => [
                            "store_in_vault" => "ON_SUCCESS",
                            "usage_type" => "MERCHANT",
                            "customer_type" => "CONSUMER"
                        ]
                    ]
                ]
            ],
        ];

        $paypal_button = [
            "payment_source" => [
                "paypal" => [
                    // Experience Context customizes the payer experience during the approval process for payment with PayPal.
                    // E.g. shipping_preference, your company brand name, user_action, payment_method_preference, locale
                    // https://developer.paypal.com/docs/api/orders/v2/#orders_create!path=payment_source/paypal/experience_context&t=request
                    "experience_context" => [
                        // Please change brand_name's value with your company name if you want to use this property.
                        "brand_name" => "Primark Stores Limited",
                        "landing_page" => "NO_PREFERENCE",
                    ]
                ]
            ],
        ];

        $paypal_advanced_vault = [
            "payment_source" => [
                "card" => [
                    "name" => "Tanguy L'Alexandre",
                    "billing_address" => [
                        "address_line_1" => "11, Rue Saint Exupery",
                        "address_line_2" => "Vannes",
                        "admin_area_1" => "Morbihan",
                        "admin_area_2" => "Anytown",
                        "postal_code" => "56000",
                        "country_code" => "FR"
                    ],
                    "attributes" => [
                        "vault" => [
                            "store_in_vault" => "ON_SUCCESS",
                            "usage_type" => "MERCHANT",
                            "customer_type" => "CONSUMER"
                        ],
                        "verification" => [
                            "method" => "SCA_ALWAYS",
                        ]
                    ],
                    "experience_context" => [
                        // Please change brand_name's value with your company name if you want to use this property.
                        "payment_method_selected" => "PAYPAL",
                        "payment_method_preference" => "IMMEDIATE_PAYMENT_REQUIRED",
                        "brand_name" => "Primark Stores Limited",
                        "landing_page" => "LOGIN",
                        "shipping_preference" => "SET_PROVIDED_ADDRESS",
                        "user_action" => "PAY_NOW",
                        "return_url" => "https://example.com/returnUrl",
                        "cancel_url" => "https://example.com/cancelUrl",
                    ],
                ]
            ]
        ];

        $paypal_advanced = [
            "payment_source" => [
                "card" => [
                    "attributes" => [
                        "verification" => [
                            "method" => "SCA_ALWAYS",
                        ]
                    ],
                ]
            ]
        ];

        // After defining whether the payment comes from the PayPal/PayLater button or from the advanced credit card form, 
        // we decide which of the two arrays to join to the main one, $data.
        if (isset($_GET['task']) && $_GET['task'] == 'button') {
            if (isset($_GET['savePayment'])) {
                $data = array_merge($data, $paypal_button_vault);
            } else {
                $data = array_merge($data, $paypal_button);
            }
        } else if (isset($_GET['task']) && $_GET['task'] == 'advancedCC') {
            $data = array_merge($data, $paypal_advanced);
            if (isset($_GET['savePayment'])) {
                $data = array_merge($data, $paypal_advanced_vault);
            }
        }

        // PayPal-Request-Id mandatory when we use payment_source in the request
        $requestid = "new-order-" . date("Y-m-d-h-i-s");

        $json = json_encode($data);

        $curl = curl_init();

        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => "https://api.sandbox.paypal.com/v2/checkout/orders/",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_HEADER => false,
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json",
                    "Authorization: Bearer " . $this->accessToken,
                    "PayPal-Request-Id: " . $requestid,
                    "Prefer: return=representation"
                ),
                CURLOPT_POSTFIELDS => $json,
            )
        );

        $response = curl_exec($curl);

        curl_close($curl);
        print_r($response);
    }


    // As we complete the 3DS challenge, the order API updates with the 3DS result.
    // This is why we need a GET order id. 
    // We call this method to retrieve the result of the 3D Secure challenge.
    // Examples of results of the 3D Secure challenge and API responses are here:
    // https://developer.paypal.com/docs/checkout/advanced/customize/3d-secure/test/
    public function paymentSource()
    {
        $this->generateAccessToken();

        if (isset($_GET['order3ds'])) {
            $order = $_GET['order3ds'];
        } else {
            $order = $_GET['catch3dserr'];
        }

        $curl = curl_init();

        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => "https://api-m.sandbox.paypal.com/v2/checkout/orders/$order?fields=payment_source",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HEADER => false,
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json",
                    "Authorization: Bearer " . $this->accessToken,
                ),
            )
        );

        $response = curl_exec($curl);

        curl_close($curl);

        $outcome = $this->responseParameters($response);

        print_r($outcome);
    }


    // With paymentSource(), we have collected the necessary information returned by the 3DS, 
    // now we can use that information to check which recommended actions correspond to, and then return the result.
    // The recommended actions will tell us whether we can proceed with payment capture, 
    // or whether to show an error message to the buyer.
    // You can find the table of Recommended Actions at this URL: 
    // https://developer.paypal.com/docs/checkout/advanced/customize/3d-secure/response-parameters/
    public function responseParameters($tds)
    {
        $json_3ds = json_decode($tds);

        isset($json_3ds->payment_source->card->authentication_result->liability_shift) ? $LS = $json_3ds->payment_source->card->authentication_result->liability_shift : $LS = "X";
        isset($json_3ds->payment_source->card->authentication_result->three_d_secure->enrollment_status) ? $ES = $json_3ds->payment_source->card->authentication_result->three_d_secure->enrollment_status : $ES = "X";
        isset($json_3ds->payment_source->card->authentication_result->three_d_secure->authentication_status) ? $AS = $json_3ds->payment_source->card->authentication_result->three_d_secure->authentication_status : $AS = "X";
        $result = array('ES' => $ES, 'AS' => $AS, 'LS' => $LS);

        // We turned the table of recommended actions into arrays for easier verification and simpler reading.
        // https://developer.paypal.com/docs/checkout/advanced/customize/3d-secure/response-parameters/#link-recommendedaction

        // Continue with authorization.
        $CWA = [
            array('ES' => 'Y', 'AS' => 'Y', 'LS' => 'POSSIBLE'),
            array('ES' => 'Y', 'AS' => 'Y', 'LS' => 'YES'),
            array('ES' => 'Y', 'AS' => 'A', 'LS' => 'POSSIBLE'),
            array('ES' => 'N', 'AS' => 'X', 'LS' => 'NO'),
            array('ES' => 'U', 'AS' => 'X', 'LS' => 'NO'),
            array('ES' => 'B', 'AS' => 'X', 'LS' => 'NO')
        ];

        // Do not continue with authorization.
        $DNCWA = [
            array('ES' => 'Y', 'AS' => 'N', 'LS' => 'NO'),
            array('ES' => 'Y', 'AS' => 'R', 'LS' => 'NO')
        ];

        // Do not continue with authorization. Request cardholder to retry.
        $DNCWARCHTR = [
            array('ES' => 'Y', 'AS' => 'U', 'LS' => 'UNKNOWN'),
            array('ES' => 'Y', 'AS' => 'U', 'LS' => 'NO'),
            array('ES' => 'Y', 'AS' => 'C', 'LS' => 'UNKNOWN'),
            array('ES' => 'Y', 'AS' => 'X', 'LS' => 'NO'),
            array('ES' => 'U', 'AS' => 'X', 'LS' => 'UNKNOWN'),
            array('ES' => 'X', 'AS' => 'X', 'LS' => 'UNKNOWN')
        ];

        if (in_array($result, $CWA)) {
            $res = ["result" => "capture"];
        } elseif (in_array($result, $DNCWA)) {
            $res = ["result" => "unknown"];
        } elseif (in_array($result, $DNCWARCHTR)) {
            $res = ["result" => "retry"];
        } else {
            $res = ["result" => "genericIssue"];
        }

        $res = json_encode($res);
        return $res;
    }

    // Capture the payment
    public function capturePayment()
    {
        global $customerID;

        $this->generateAccessToken();

        // If we pay through the button we need the order ID passed as parameter in the URL
        if (isset($_GET['order'])) $this->orderId = $_GET['order'];
        if (isset($_GET['capture'])) $this->orderId = $_GET['capture'];

        $curl = curl_init();

        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => "https://api.sandbox.paypal.com/v2/checkout/orders/" . $this->orderId . "/capture",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_HEADER => false,
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json",
                    "Authorization: Bearer " . $this->accessToken,
                    "Prefer: return=representation"
                ),
            )
        );

        $response = curl_exec($curl);
        curl_close($curl);
        $jsonResponse = json_decode($response);
        if (isset($_COOKIE['customerID']) === false) {
            $customerID = $jsonResponse->payment_source->paypal->attributes->vault->customer->id;
            setcookie('customerID', $customerID, time() + (86400 * 30), "/");
        } else {
            $customerID = "";
        }
        print_r($response);
    }
}   // EOC



$myOrder = new MyOrder();
// Generate access token for Vaults
if (isset($_GET['token'])) $myOrder->generateAccessToken();


// Create order through PayPal Button or Credit Card
if (isset($_GET['task'])) $myOrder->createOrder();

// Capture payment placed through the PayPal Button
if (isset($_GET['order']) || isset($_GET['capture'])) $myOrder->capturePayment();

//Checking the 3DS Response, either when it fails or when it's successful 
if (isset($_GET['catch3dserr']) || isset($_GET['order3ds'])) $myOrder->paymentSource();
