$(".relatorio").click(function (event) {
    event.preventDefault();

    $('#relatorio').show();
});

$("#formRelatorio").submit(function (event) {
    event.preventDefault();

    const inflacao = parseFloat($("#inflacao").val());
    const id_predio = $("#predio").val();
    const ano = $("#anoPesquisa").val();
    const mes = $("#mesPesquisa").val();

    if (isNaN(inflacao) || !id_predio) {
        alert("Por favor, insira os dados corretamente.");
        return;
    }

    const data = {
        inflacao: inflacao,
        id_predio: id_predio,
        ano: ano,
        mes: mes
    };

    $.ajax({
        url: '../php/script/pdf.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(data),
        xhrFields: {
            responseType: 'blob'
        },
        success: function(response) {
            const blob = new Blob([response], { type: 'application/pdf' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'relatorio.pdf';
            link.click();
            URL.revokeObjectURL(link.href);
        },
        error: function(xhr, status, error) {
            console.error("Erro ao enviar os dados: ", error);
        }
    });
    
    

});

