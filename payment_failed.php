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

<?php
    $retry = "3d Secure Failed (Request cardholder to retry): <br><br>An error occurred during the transaction. Please try again.";
    $failed3DS = "3d Secure Failed (Do not continue with authorization):<br><br>An unknown error occurred during the transaction. Please consult your credit card issuer.";
    $backendIssue = "An issue to identify prevented the capture of the payment.";
    $unknownError = "Order not created: please retry or try another payment method, thank you.";
    $message = "No error message received.";

    if(isset($_GET['result'])) {
        if($_GET['result'] == 'unknown') $message = $failed3DS;
        if($_GET['result'] == 'retry') $message = $retry;
        if($_GET['result'] == 'genericIssue') $message = $backendIssue;
        if($_GET['result'] != 'genericIssue' && $_GET['result'] != 'retry' && $_GET['result'] != 'unknown') $message = $unknownError;
    } elseif(isset($_GET['error'])) {
        $message = "Please make sure that you have created an Order ID. <br><br>API Error message: " . $_GET['error'];
    } else {
        $message = "An unknown error has occurred";
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Checkout Integration with 3D Secure and SDK v2</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: .9rem;
        }
    </style>
</head>

<body>

    <!-- This container will show an error message in case something goes wrong during CC payment -->
    <div style="width:50%; margin-left: 10rem; margin-top: 3rem; display: block" id="event-error">
        <a href="index.php" style="font-size: 0.7rem;">Back to the index</a>
        <h4 style="color: #FF0000;" id="error-title">Error during the transaction</h4>
        <pre id="error-json" style="font-size: 0.9rem; color: #32424e; margin: 0;"><?= $message ?></pre>
    </div>

</body>
</html>