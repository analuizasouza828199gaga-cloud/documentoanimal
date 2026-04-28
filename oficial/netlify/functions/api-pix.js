exports.handler = async function (event) {
  const headers = {
    "Content-Type": "application/json; charset=utf-8",
    "Access-Control-Allow-Origin": "*",
    "Access-Control-Allow-Headers": "Content-Type",
    "Access-Control-Allow-Methods": "POST, OPTIONS"
  };

  if (event.httpMethod === "OPTIONS") {
    return {
      statusCode: 200,
      headers,
      body: ""
    };
  }

  if (event.httpMethod !== "POST") {
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
    const url = "https://api.blackcatpay.com.br/api/sales/create-sale";

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

    const input = JSON.parse(event.body || "{}");

    const nome = String(input.nome || "").trim();
    const cpf = String(input.cpf || "").replace(/\D/g, "");
    const email = String(input.email || "").trim();
    const whatsapp = String(input.whatsapp || "").replace(/\D/g, "");
    const valor = input.valor || 0;
    const descricao = String(input.descricao || "Pagamento via PIX").trim();

    if (!nome || !cpf || !email || !whatsapp || !valor) {
      return {
        statusCode: 400,
        headers,
        body: JSON.stringify({
          success: false,
          message: "Dados obrigatórios ausentes.",
          recebido: input
        })
      };
    }

    const valorCentavos = Math.round(Number(valor) * 100);

    const payload = {
      amount: valorCentavos,
      currency: "BRL",
      paymentMethod: "pix",
      items: [
        {
          title: descricao,
          unitPrice: valorCentavos,
          quantity: 1,
          tangible: false
        }
      ],
      customer: {
        name: nome,
        email: email,
        phone: whatsapp,
        document: {
          number: cpf,
          type: "cpf"
        }
      },
      pix: {
        expiresInDays: 1
      }
    };

    const response = await fetch(url, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "Accept": "application/json",
        "X-API-Key": apiKey
      },
      body: JSON.stringify(payload)
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

    const transactionId = data?.data?.transactionId || null;
    const status = data?.data?.status || null;
    const qrCode = data?.data?.paymentData?.qrCode || null;
    const qrCodeBase64 = data?.data?.paymentData?.qrCodeBase64 || null;
    const copyPaste = data?.data?.paymentData?.copyPaste || qrCode || null;
    const expiresAt = data?.data?.paymentData?.expiresAt || null;
    const invoiceUrl = data?.data?.invoiceUrl || null;

    return {
      statusCode: httpCode,
      headers,
      body: JSON.stringify({
        success: data?.success ?? (httpCode >= 200 && httpCode < 300),
        httpCode: httpCode,
        transaction_id: transactionId,
        status: status,
        qr_code: qrCode,
        qr_code_base64: qrCodeBase64,
        pix_copia_cola: copyPaste,
        expires_at: expiresAt,
        invoice_url: invoiceUrl,
        api_response: data
      })
    };

  } catch (error) {
    return {
      statusCode: 500,
      headers,
      body: JSON.stringify({
        success: false,
        message: "Erro ao gerar PIX.",
        error: error.message
      })
    };
  }
};

