$(".cadastro").click(function (event) {
    event.preventDefault();

    var sDados = {
        email: $(".email").val().trim(),
        senha: $(".senha").val().trim()
    };

    if (sDados.email !== '' && sDados.senha !== '') {
        $.ajax({
            method: "POST",
            url: "../login.php",
            data: sDados,
            dataType: "json"
        })
        .done(function(resposta) {
            console.log("Resposta da verificação de Email e Senha:", resposta);
            if (resposta.email === 'correto' && resposta.senha === 'correto') {
                window.location.href = "sistema.php";
            } else {
                if (resposta.email === 'incorreto') {
                    $(".resultado").html('Email incorreto! Verifique sua entrada.');
                } else if (resposta.senha === 'incorreto') {
                    $(".resultado").html('Senha incorreta! Tente novamente.');
                } else {
                    $(".resultado").html('Erro desconhecido. Tente novamente.');
                }
            }
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
            console.error("Erro na requisição AJAX:", textStatus, errorThrown);
            $(".resultado").html('Ocorreu um erro ao processar sua solicitação. Tente novamente mais tarde.');
        });
    } else {
        $(".resultado").html('Por favor, preencha todos os campos.');
    }
});
