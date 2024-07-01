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

/* Buttons */
let saveBtn = document.getElementById('save');
let savePayment;
paypal
  .Buttons({
    // When PayPal button is clicked, an order is created on the server 
    //  and the API returns the Order ID.
    // We need the Order ID to "Capture" the payment
    // Please check all the JavaScript events available here:
    // https://developer.paypal.com/sdk/js/reference/#link-paypalbuttonsoptions
    createOrder: function (data, actions) {
      const url = `backend.php?task=button${saveBtn.checked ? '&savePayment' : ''}`;
      return fetch(url, {
        method: "post",
      })
        .then((response) => response.json())
        .then((order) => order.id);
    },

    // Once the buyer is inside the Popup, they need to click on "Complete Purchase"
    //  to complete the payment. That triggers the event onApprove.
    // The event onApprove will pass the Order ID to your backend, where you will capture the payment.
    onApprove: function (data, actions) {
      return fetch(`backend.php?order=${data.orderID}`, {
        method: "post",
      })
        .then((response) => response.json())
        .then((orderData) => {
          // This code example only considers successful captures.
          // You would need to check the Capture Status and take action accordingly:
          // Please visit the following page and write code to manage any of these possible circumstances
          // https://developer.paypal.com/docs/api/orders/v2/#definition-capture_status
          // The following code will be replacing the checkout with a successful message and the API response for the developer.
          let div_mycart = document.getElementById('mycart');
          let div_title = document.getElementById("title");
          let div_response = document.getElementById('api-response');
          let div_json = document.getElementById('api-json');
          let div_api_title = document.getElementById("api-title");
          let res = JSON.stringify(orderData, null, 2);

          div_mycart.style.display = "none";
          div_response.style.display = "block";
          div_json.innerHTML = res;
          div_title.style.color = "#009cde";
          div_title.innerHTML = "Transaction completed.";
          div_api_title.innerHTML = "API response:";
        });
    },

    /*

      NOTE: More events to implement:
      Click here: https://developer.paypal.com/sdk/js/reference/#link-paypalbuttonsoptions
      
      onCancel(data) {
          // Show a cancel page, or return to cart
          window.location.assign("/your-cancel-page");
      },  
    
      onError(err) {
          // For example, redirect to a specific error page
          window.location.assign("/your-error-page-here");
      },

      onInit(data, actions) {
        ...
      },
      
      onClick() {
        ...
      },      

    */

  }).render("#paypal-button-container");



/* Advanced Credit Card Form - cardFields */

// A variable to save the Order ID that PayPal creates for us
let GlorderID;

// Create the Card Fields Component and define callbacks
const cardField = paypal.CardFields({

  createOrder: function (data) {
    const url =`backend.php?task=advancedCC${saveBtn.checked ? '&savePayment' : ''}`
    return fetch(url)
      .then((res) => {
        return res.json();
      })
      .then((orderData) => {
        // Assigning the Order ID received to GlorderID
        // We'll need it in case of errors
        GlorderID = orderData.id;
        // Returning Order ID for the event onApprove
        return orderData.id;
      });
  },

  // The 3D Secure challenge starts soon after the event createOrder and right before onApprove
  // If the buyer does not pass the challenge, the event onError is called. Otherwise onApprove starts.
  // Finalize the transaction after a successfull 3D Secure challenge
  onApprove: function (data) {
    const { orderID } = data;
    // Checking 3D Secure result.
    // By default, we reach this point only if the buyer successfully passes the 3D Secure challenge
    //  but it's good practice to make sure that the result is positive.
    return fetch(`backend.php?order3ds=${orderID}`)
      .then((res) => {
        return res.json();
      })
      .then((orderData) => {
        // Our fetch().then() returns orderData that stores data from your backend
        let result;

        // orderData.result is the string "capture" that we are expecting from the backend
        //  to confirm that the buyer has passed the 3D Secure challenge
        if (orderData.result) result = orderData.result;

        if (result == "capture") {
          // Now we can capture the payment using the Order ID received
          // The backend will send an API request to the server
          return fetch(`backend.php?capture=${orderID}`)
            .then((res) => {
              return res.json();
            })
            .then((captureData) => {
              // Frontend to replace with a successful message
              // You could use a redirect instead
              let div_mycart = document.getElementById('mycart');
              let div_title = document.getElementById("title");
              let div_response = document.getElementById('api-response');
              let div_json = document.getElementById('api-json');
              let div_api_title = document.getElementById('api-title');
              let res = JSON.stringify(captureData, null, 2);
              let success = "Transaction completed.";
              let failed = "The payment for this order could not be captured. Please try another payment method.";

              // captureData contains the API Response 
              // We assign the status of our payment that is in the API Response to the variable "status"
              // A positive status would be "COMPLETED"            
              let status = captureData.purchase_units[0].payments.captures[0].status;
              // Let's now check the status and proceed
              status = JSON.stringify(status);

              // This code example only considers successful captures.
              // You would need to check the Capture Status and take action accordingly:
              // Please visit the following page and write code to manage any of these possible circumstances
              // https://developer.paypal.com/docs/api/orders/v2/#definition-capture_status
              // The following code will be replacing the checkout with a successful message and the full API response for the developer
              if (status.replaceAll('"', '') == "COMPLETED") {
                // Once we make sure that the outcome of our transaction is "COMPLETED"
                //  we show a positive message to the buyer      
                div_mycart.style.display = "none";
                div_response.style.display = "block";
                div_title.innerHTML = success;
                div_title.style.color = "#009cde";
                div_api_title.innerHTML = "API response:";
                div_json.innerHTML = res;
              } else {
                // Else, If the status is DECLINED or FAILED etc.
                // PENDING is not a failed transaction yet, it might change to COMPLETED or DECLINED within 24/48h
                div_mycart.style.display = "none";
                div_response.style.display = "block";
                div_title.innerHTML = failed;
              }
            });
        } else {
          // If orderData.result is not "capture"
          console.log("An issue with the 3D Secure occurred.")
        }
      });
  },

  onError: function (err) {
    // We reach this point if the buyer did not pass the 3D Secure
    // GlorderID, the variable with our Order ID that we have saved during the createOrder
    if (GlorderID) {
      return fetch(`backend.php?catch3dserr=${GlorderID}`)
        .then((response) => response.json())
        .then((tdsresponse) => {
          let result;
          if (tdsresponse.result) result = tdsresponse.result;

          // Redirecting to an error/payment_failed page
          window.location.href = 'payment_failed.php?result=' + result;
        });
    } else {
      // Any other SDK error not related to the 3D Secure will be managed here
      window.location.href = 'payment_failed.php?error=' + err;
    }
  },
});


// Render each field after checking for eligibility
if (cardField.isEligible()) {

  const nameField = cardField.NameField();
  nameField.render('#card-name-field-container');

  const numberField = cardField.NumberField();
  numberField.render('#card-number-field-container');

  const cvvField = cardField.CVVField();
  cvvField.render('#card-cvv-field-container');

  const expiryField = cardField.ExpiryField();
  expiryField.render('#card-expiry-field-container');

  // Adding click listener to submit button and calling the submit function on the CardField component
  document.getElementById("card-field-submit-button").addEventListener("click", () => {
    cardField
      .submit({
        // This is the buyer billing address that will be used for the 3D Secure
        // You either could give the buyer the chance to enter a billing address in a form
        // or take it from a database in the case you already have it.
        // Do not disregard the billing address.        
        // Cardholder's first and last name
        name: "Walter White",
        // Billing Address 
        billingAddress: {
          // Street address, line 1
          address_line_1: "via della notte 12",
          // Street address, line 2 (Ex: Unit, Apartment, etc.)
          address_line_2: "Parco Bello",
          // City
          admin_area_2: "Bologna",
          // State
          admin_area_1: "BO",
          // Postal Code
          postal_code: "00020",
          // Country Code Format must be: https://developer.paypal.com/reference/country-codes/ 
          country_code: "FR",
        },
      })
      .catch((err) => {
        // If we receive any error not related to the SDK
        // Check the JavaScript Console
        console.log("An error has occurred:");
        console.log(err);
      });
  });
};
