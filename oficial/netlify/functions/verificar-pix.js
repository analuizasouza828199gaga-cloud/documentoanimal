exports.handler = async function (event) {
  const headers = {
    "Content-Type": "application/json; charset=utf-8",
    "Access-Control-Allow-Origin": "*",
    "Access-Control-Allow-Headers": "Content-Type",
    "Access-Control-Allow-Methods": "GET, OPTIONS"
  };

  if (event.httpMethod === "OPTIONS") {
    return {
      statusCode: 200,
      headers,
      body: ""
    };
  }

  if (event.httpMethod !== "GET") {
    return {
      statusCode: 405,
      headers,
      body: JSON.stringify({
        success: false,
        message: "Método não permitido."
      })
    };
  }

  try {
    const apiKey = process.env.BLACKCAT_API_KEY;

    if (!apiKey) {
      return {
        statusCode: 500,
        headers,
        body: JSON.stringify({
          success: false,
          message: "Chave da API não configurada na Netlify."
        })
      };
    }

    const params = event.queryStringParameters || {};
    const transactionId = String(params.transactionId || params.hash || "").trim();

    if (!transactionId) {
      return {
        statusCode: 400,
        headers,
        body: JSON.stringify({
          success: false,
          message: "transactionId não informado."
        })
      };
    }

    const url =
      "https://api.blackcatpay.com.br/api/sales/" +
      encodeURIComponent(transactionId) +
      "/status";

    const response = await fetch(url, {
      method: "GET",
      headers: {
        "Accept": "application/json",
        "Content-Type": "application/json",
        "X-API-Key": apiKey
      }
    });

    const httpCode = response.status;
    const text = await response.text();

    let data;

    try {
      data = JSON.parse(text);
    } catch (e) {
      return {
        statusCode: 500,
        headers,
        body: JSON.stringify({
          success: false,
          message: "Resposta inválida da API.",
          raw_response: text
        })
      };
    }

    const status = String(data?.data?.status || "").toUpperCase();
    const pago = status === "PAID";

    return {
      statusCode: httpCode,
      headers,
      body: JSON.stringify({
        success: data?.success ?? (httpCode >= 200 && httpCode < 300),
        httpCode: httpCode,
        transactionId: data?.data?.transactionId || transactionId,
        status: status,
        pago: pago,
        paymentMethod: data?.data?.paymentMethod || null,
        amount: data?.data?.amount || null,
        netAmount: data?.data?.netAmount || null,
        fees: data?.data?.fees || null,
        paidAt: data?.data?.paidAt || null,
        endToEndId: data?.data?.endToEndId || null,
        api_response: data
      })
    };

  } catch (error) {
    return {
      statusCode: 500,
      headers,
      body: JSON.stringify({
        success: false,
        message: "Erro ao consultar transação.",
        error: error.message
      })
    };
  }
};

