exports.handler = async function (event) {
  try {
    if (event.httpMethod !== "POST") {
      return {
        statusCode: 405,
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          success: false,
          message: "Método não permitido. Use POST."
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
          message: "Chave da API não configurada na Netlify."
        })
      };
    }

    const input = JSON.parse(event.body || "{}");

    const nome = String(input.nome || "").trim();
    const cpf = String(input.cpf || "").replace(/\D/g, "");
    const email = String(input.email || "").trim();
    const whatsapp = String(input.whatsapp || "").replace(/\D/g, "");
    const valor = Number(input.valor || 0);
    const descricao = String(input.descricao || "Pagamento via PIX").trim();

    if (!nome || !cpf || !email || !whatsapp || !valor) {
      return {
        statusCode: 400,
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          success: false,
          message: "Dados obrigatórios ausentes.",
          recebido: input
        })
      };
    }

    const valorCentavos = Math.round(valor * 100);

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

    const response = await fetch("https://api.blackcatpay.com.br/api/sales/create-sale", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "Accept": "application/json",
        "X-API-Key": apiKey
      },
      body: JSON.stringify(payload)
    });

    const rawResponse = await response.text();

    let data;
    try {
      data = JSON.parse(rawResponse);
    } catch (e) {
      return {
        statusCode: 500,
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          success: false,
          message: "Resposta inválida da API BlackCatPay.",
          raw_response: rawResponse
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
      statusCode: response.status,
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        success: data?.success ?? response.ok,
        httpCode: response.status,
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
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        success: false,
        message: "Erro interno ao gerar PIX.",
        error: error.message
      })
    };
  }
};
