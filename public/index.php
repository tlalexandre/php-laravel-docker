<!--
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
-->

<?php require_once "backend.php";
// Instantiate the MyOrder class and generate the access token
$order = new MyOrder();
$order->generateAccessToken();

// Assuming $userIDToken is set as a global variable in backend.php
global $userIDToken;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- The following CSS file is a sample for demo purposes. Instead, you should use styles that align with your brand 
    using the CSS properties supported by this integration: https://developer.paypal.com/docs/checkout/advanced/customize/card-field-style/ -->
    <link rel="stylesheet" type="text/css" href="https://www.paypalobjects.com/webstatic/en_US/developer/docs/css/cardfields.css" />
    <link rel="stylesheet" href="style.css">
    <title>Advanced Checkout Integration with 3D Secure and SDK v2</title>
    <!-- &buyer-country is available only in SandBox for testing, remove it before going Live -->
    <!-- Check all the parameters and the JavaScript SDK script configuration at the following link: -->
    <!-- https://developer.paypal.com/sdk/js/configuration/ -->
    <script src="https://www.paypal.com/sdk/js?components=messages,buttons,card-fields&enable-funding=paylater&buyer-country=FR&currency=EUR&client-id=<?php echo $CLIENT_ID; ?>" data-user-id-token=<?= $userIDToken ?>></script>
</head>

<body>

    <div id="mycart">
        <!-- Pay Later Messages:
             https://developer.paypal.com/docs/checkout/pay-later/gb/integrate/#link-addpaylatermessages
             Please replace the amount with your variable -->
        <div data-pp-message data-pp-amount="240.00" data-pp-layout="text"></div>

        <!-- PayPal Buttons:
             https://developer.paypal.com/docs/checkout/advanced/integrate/ -->
        <div id="paypal-button-container" class="paypal-button-container"></div>

        <br />
        <h2>Credit Card</h2>
        <div id="checkout-form">
            <div id="card-name-field-container"></div>
            <div id="card-number-field-container"></div>
            <div id="card-expiry-field-container"></div>
            <div id="card-cvv-field-container"></div>
            <button value="submit" id="card-field-submit-button" class="nbtn" type="button">Pay</button>
        </div>

        <br /><br />
    </div>

    <!-- This container will show the API response, and will be available after the transaction -->
    <div id="api-response">
        <a href="index.php" style="font-size: 0.7rem;">Back to the index</a>
        <h4 style="color: #FF0000; padding: 1.2rem 0;" id="title"></h4>
        <h6 style="font-size: 0.9rem; color: #32424e; margin: 0; padding: 0;" id="api-title"></h6>
        <pre id="api-json" style="font-size: 0.9rem; color: #32424e; padding-bottom: 2rem;"></pre>
    </div>

    <div class="checkbox">
        <input type="checkbox" id="save" name="save">
        <label for="save">Save your card</label>
    </div>

    <!-- Javascript file that includes our buttons and cardField events -->
    <script src="app.js"></script>
</body>

</html>