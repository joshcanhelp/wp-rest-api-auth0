async function checkWordPressForUser(user, context, callback) {
  if (!user.email) {
    console.log("User does not have an email to use.");
    return callback(null, user, context);
  }

  const customClaimNamespace = "https://custom-claim/has_wp_account";
  const {
    WP_API_CLIENT_ID,
    WP_API_IDENTIFIER,
    WP_API_GET_USER_URL,
    WP_API_TOKEN,
  } = configuration;

  if (!WP_API_IDENTIFIER || !WP_API_GET_USER_URL || !WP_API_TOKEN) {
    console.log("Missing required configuration.");
    return callback(null, user, context);
  }

  if (WP_API_CLIENT_ID && context.clientID === WP_API_CLIENT_ID) {
    console.log("Logging into WP application, skipping check.");
    return callback(null, user, context);
  }

  const { query = {} } = context.request || {};
  if (!query.audience || query.audience !== WP_API_IDENTIFIER) {
    console.log(`Not the WP API audience: ${query.audience}`);
    return callback(null, user, context);
  }

  context.idToken[customClaimNamespace] = false;

  const axios = require("axios@0.19.2");
  const url = require("url");
  const formData = new url.URLSearchParams({ username: user.email });

  let apiResponse;
  try {
    apiResponse = await axios.post(WP_API_GET_USER_URL, formData.toString(), {
      headers: {
        Authorization: `Bearer ${WP_API_TOKEN}`,
        "Content-Type": "application/x-www-form-urlencoded",
      },
    });
  } catch (apiHttpError) {
    console.log(`Error calling the WP API: ${apiHttpError.message}`);
    return callback(null, user, context);
  }

  if (apiResponse.data.error) {
    console.log(`Error returned from the WP API: ${apiResponse.data.error}`);
    return callback(null, user, context);
  }

  if (apiResponse.data.ID) {
    context.idToken[customClaimNamespace] = true;
  }

  return callback(null, user, context);
}
