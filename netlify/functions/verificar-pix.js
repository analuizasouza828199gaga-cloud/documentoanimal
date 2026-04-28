exports.handler = async function (event) {
  try {
    if (event.httpMethod !== "GET") {
      return {
        statusCode: 405,
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          success: false,
          pago: false,
          message: "Método não permitido. Use GET."
        })
      };
    }

    const apiKey = process.env.BLACKCAT_API_KEY;

    if (!apiKey) {
      return {
        statusCode: 500,
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          success: false,
          pago: false,
          message: "Chave da API não configurada na Netlify."
        })
      };
    }

    const transactionId = event.queryStringParameters?.transactionId;

    if (!transactionId) {
      return {
        statusCode: 400,
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          success: false,
          pago: false,
          message: "transactionId ausente."
        })
      };
    }

    const url = `https://api.blackcatpay.com.br/api/sales/${encodeURIComponent(transactionId)}/status`;

    const response = await fetch(url, {
      method: "GET",
      headers: {
        "Accept": "application/json",
        "X-API-Key": apiKey
      }
    });

    const rawResponse = await response.text();

    let data = {};
    try {
      data = rawResponse ? JSON.parse(rawResponse) : {};
    } catch (e) {
      return {
        statusCode: 500,
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          success: false,
          pago: false,
          message: "Resposta inválida da API BlackCatPay.",
          raw_response: rawResponse
        })
      };
    }

    const status = data?.data?.status || "";
    const statusNormalizado = String(status).toUpperCase();

    const pago = statusNormalizado === "PAID";

    return {
      statusCode: 200,
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        success: data?.success === true,
        pago: pago,
        status: status,
        transaction_id: data?.data?.transactionId || transactionId,
        paid_at: data?.data?.paidAt || null,
        amount: data?.data?.amount || null,
        api_response: data
      })
    };

  } catch (error) {
    return {
      statusCode: 500,
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        success: false,
        pago: false,
        message: "Erro interno ao verificar pagamento.",
        error: error.message
      })
    };
  }
};
