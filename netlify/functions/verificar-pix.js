exports.handler = async function (event) {
  try {
    if (event.httpMethod !== "GET") {
      return {
        statusCode: 405,
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          success: false,
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

    const url = `https://api.blackcatpay.com.br/api/sales/${encodeURIComponent(transactionId)}`;

    const response = await fetch(url, {
      method: "GET",
      headers: {
        "Accept": "application/json",
        "X-API-Key": apiKey
      }
    });

    const rawResponse = await response.text();

    let data;
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

    const status =
      data?.data?.status ||
      data?.status ||
      data?.data?.sale?.status ||
      "";

    const statusNormalizado = String(status).toLowerCase();

    const pago = [
      "paid",
      "approved",
      "completed",
      "confirmed",
      "success",
      "succeeded",
      "pago",
      "aprovado"
    ].includes(statusNormalizado);

    return {
      statusCode: 200,
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        success: true,
        pago: pago,
        status: status,
        transaction_id: transactionId,
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
