<html>
<script src="https://stg-id.singpass.gov.sg/static/ndi_embedded_auth.js"></script>
<script>
  async function init() {
    const authParamsSupplier = async () => {
      // Replace the below with an `await`ed call to initiate an auth session on your backend
      // which will generate state+nonce values, e.g
      return { state: "dummySessionState", nonce: "dummySessionNonce" };
    };

    const onError = (errorId, message) => {
      console.log(`onError. errorId:${errorId} message:${message}`);
    };

    const initAuthSessionResponse = window.NDI.initAuthSession(
      'ndi-qr',
      {
        clientId: '{{ env("SINGPASS_CLIENT_ID") }}', // Replace with your client ID
        redirectUri: '{{ env("SINGPASS_REDIRECT_URL") }}',        // Replace with a registered redirect URI
        scope: 'openid',
        responseType: 'code'
      },
      authParamsSupplier,
      onError
    );

    console.log('initAuthSession: ', initAuthSessionResponse);
  }
</script>
<body onload="init()">
<div id="ndi-qr"></div>
</body>
</html>