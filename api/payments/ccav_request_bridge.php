<?php
$encRequest = $_GET['encRequest'] ?? '';
$access_code = $_GET['access_code'] ?? '';
?>
<html>
<head>
    <title>Redirecting to Payment Gateway...</title>
</head>
<body onload="document.payment_form.submit();">
    <div style="text-align: center; margin-top: 100px;">
        <h2>Connecting to Secure Payment Gateway...</h2>
        <p>Please do not refresh the page or click the back button.</p>
    </div>
    <form method="post" name="payment_form" action="https://secure.ccavenue.com/transaction/transaction.do?command=initiateTransaction">
        <input type="hidden" name="encRequest" value="<?php echo $encRequest; ?>">
        <input type="hidden" name="access_code" value="<?php echo $access_code; ?>">
    </form>
</body>
</html>
