
function hideAllPopups() {
    $(".popup").hide();
    $(".popupEdita").hide();
    $(".popupConfirma").hide();
}

function formatarData(data) {
    let dia, mes, ano;

    if (data.match(/^\d{4}-\d{2}-\d{2}$/)) {
        [dia, mes, ano] = data.split("-");
    }
    else if (data.match(/^\d{4}\/\d{2}\/\d{2}$/)) {
        [dia, mes, ano] = data.split("/");
    }
    else if (data.match(/^\d{2}\/\d{2}\/\d{4}$/)) {
        return data;
    }
    else if (data.match(/^\d{2}-\d{2}-\d{4}$/)) {
        [dia, mes, ano] = data.split("-");
    } else {
        return data;
    }

    return `${ano}/${mes}/${dia}`;
}


function desformatarData(data) {
    let dia, mes, ano;

    if (data.match(/^\d{2}-\d{2}-\d{4}$/)) {
        [dia, mes, ano] = data.split("-");
    }
    else if (data.match(/^\d{2}\/\d{2}\/\d{4}$/)) {
        [dia, mes, ano] = data.split("/");
    }
    else if (data.match(/^\d{4}\/\d{2}\/\d{2}$/)) {
        [dia, mes, ano] = data.split("/");
    }
    else if (data.match(/^\d{4}-\d{2}-\d{2}$/)) {
        return data;
    }
    else {
        console.error("Formato de data inválido. Use 'yyyy-mm-dd', 'yyyy/mm/dd' ou 'dd/mm/yyyy'.");
        return null;
    }

    return `${ano}-${mes}-${dia}`;
}

function formatarTelefone(telefone) {
    telefone = telefone.replace(/\D/g, '');

    if (telefone.length === 11) {
        telefone = telefone.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
    } else if (telefone.length === 10) {
        telefone = telefone.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
    }

    return telefone;
}

function setupTableActions(tableType) {
    const capitalize = (str) => str.charAt(0).toUpperCase() + str.slice(1);

    $(`.${tableType}-adicionar`).click(function () {
        hideAllPopups();
        $(`#popup-${tableType}`).show();
    });

    $(".close").click(hideAllPopups);

    $(`.${tableType}-editar`).click(function () {
        hideAllPopups();
        $(`#popupEdita-${tableType}`).show();

        const row = $(this).closest('tr');
        const id = row.find('td').eq(0).text();
        const nome = row.find('td').eq(1).text();

        $(`#editId${capitalize(tableType)}`).val(id);
        $("#editNome").val(nome);

        if (tableType === 'sindico') {
            $("#editEmail").val(row.find('td').eq(3).text());
            $("#editCpf").val(row.find('td').eq(2).text());
        }

        if (tableType === 'morador') {
            $("#editTelefone").val(row.find('td').eq(3).text());
            $("#editCpf").val(row.find('td').eq(2).text());
        }

        if (tableType === 'predios') {
            var idSindico = row.find('td[data-id]').data('id');
            $("#editCep").val(row.find('td').eq(3).text());
            $("#editCidade").val(row.find('td').eq(4).text());
            $("#editBairro").val(row.find('td').eq(5).text());
            $("#editRua").val(row.find('td').eq(6).text());
            $("#editNumero").val(row.find('td').eq(7).text());

            $("#editIdSindico").val(idSindico);
        }

        if (tableType === 'despesas') {
            var idPredio = row.find('td[data-id]').data('id');
            var idContato = row.find('td[data-id]').data('id');

            $("#editIdPredio").val(idPredio);
            $("#editIdContato").val(idContato);

            const nome = row.find('td').eq(1).text();
            const valorTotal = row.find('td').eq(5).text().replace("R$ ", "").trim().replace(",", ".");
            const formattedValorTotal = parseFloat(valorTotal).toFixed(2).replace(".", ",");

            const vencimento = row.find('td').eq(6).text().trim();
            formatadovencimento = desformatarData(vencimento)
            $("#editVencimento").val(formatadovencimento);

            $("#editNome").val(nome);
            $("#editValor-total").val("R$ " + formattedValorTotal);
        }

        if (tableType === 'contatos') {
            const idPredio = row.find('td.id-predio').data('id');
            const telefoneRaw = row.find('td').eq(3).text();

            $("#editNome").val(row.find('td').eq(1).text());
            $("#editIdPredio").val(idPredio);
            $("#editTelefone").val(formatarTelefone(telefoneRaw));
            $("#editCep").val(row.find('td').eq(4).text());
            $("#editCidade").val(row.find('td').eq(5).text());
            $("#editBairro").val(row.find('td').eq(6).text());
            $("#editRua").val(row.find('td').eq(7).text());
            $("#editNumero").val(row.find('td').eq(8).text());
        }

        if (tableType === 'apartamentos') {
            var idPredio = row.find('td[data-id]').data('id');
            var idMorador = row.find('td[data-id]').data('id');

            $("#editIdMorador").val(idMorador);
            $("#editIdPredio").val(idPredio);
            const numero = row.find('td').eq(2).text();
            const andar = row.find('td').eq(3).text();

            $("#editNumero").val(numero);
            $("#editAndar").val(andar);
        }
    });

    $(`.${tableType}-apagar`).click(function () {
        if (tableType === 'apartamentos' && tableType === 'despesa_apartamento') {
            hideAllPopups();
            const id = $(this).closest('tr').find('td').eq(1).text();
            if (confirm("Deseja realmente apagar este item?")) {
                submitForm(tableType, { id }, 'DELETE');
            }
        } else {
            hideAllPopups();
            const id = $(this).closest('tr').find('td').eq(0).text();
            if (confirm("Deseja realmente apagar este item?")) {
                submitForm(tableType, { id }, 'DELETE');
            }
        }
    });

    $(`#add${capitalize(tableType)}Form`).submit(function (e) {
        e.preventDefault();
        const data = buildFormData(tableType, 'add');
        submitForm(tableType, data, 'POST');
    });

    $(`#edit${capitalize(tableType)}Form`).submit(function (e) {
        e.preventDefault();
        
        const data = buildFormData(tableType, 'edit');

        console.log('Dados antes do envio: ', data);
        
        submitForm(tableType, data, 'PUT');
    });
    

    $(`.check${capitalize(tableType)}`).click(function () {
        if (tableType === 'predios') {
            hideAllPopups();
            const id = $(this).closest('tr').find('td').eq(0).text();
            if (confirm("Deseja realmente ativar este prédio?")) {
                submitForm(tableType, { id }, 'CHECK');
            }
        } else if (tableType === 'despesas') {
            hideAllPopups();
            const id = $(this).closest('tr').find('td').eq(0).text();
            if (confirm("Deseja realmente pagar este item?")) {
                submitForm(tableType, { id }, 'CHECK');
            }
        } else if (tableType === 'apartamentos') {
            hideAllPopups();
            const id = $(this).closest('tr').find('td').eq(0).text();
            if (confirm("Deseja realmente ativar este apartamento?")) {
                submitForm(tableType, { id }, 'CHECK');
            }
        }

    });

    $(`.desativar${capitalize(tableType)}`).click(function () {
        if (tableType === 'predios') {
            hideAllPopups();
            const id = $(this).closest('tr').find('td').eq(0).text();
            if (confirm("Deseja realmente desativar este prédio?")) {
                submitForm(tableType, { id }, 'DESATIVE');
            }
        } else if (tableType === 'despesas') {
            hideAllPopups();
            const id = $(this).closest('tr').find('td').eq(0).text();
            if (confirm("Deseja realmente retirar este item de pago?")) {
                submitForm(tableType, { id }, 'DESATIVE');
            }
        } else if (tableType === 'apartamentos') {
            hideAllPopups();
            const id = $(this).closest('tr').find('td').eq(0).text();
            if (confirm("Deseja realmente desativar este apartamento?")) {
                submitForm(tableType, { id }, 'DESATIVE');
            }
        }

    });

    $(`#addDespesa_ApartamentoForm`).submit(function (e) {
        e.preventDefault();
        const data = buildFormData('despesa_apartamento', 'add');
        submitForm('despesa_apartamento', data, 'POST');
    })
    $(`#editDespesa_ApartamentoForm`).submit(function (e) {
        e.preventDefault();
        const data = buildFormData('despesa_apartamento', 'edit');
        submitForm('despesa_apartamento', data, 'PUT');
    })
}

function buildFormData(tableType, formType) {
    const data = {};

    if (tableType === 'sindico') {
        data.id = formType === 'edit' ? $(`#editIdSindico`).val() : undefined;
        data.nome = $(`#${formType === 'edit' ? 'editNome' : 'nome'}`).val();
        data.cpf = $(`#${formType === 'edit' ? 'editCpf' : 'cpf'}`).val();
        data.email = $(`#${formType === 'edit' ? 'editEmail' : 'email'}`).val();
        data.senha = $(`#${formType === 'edit' ? 'editSenha' : 'senha'}`).val();
    }

    if (tableType === 'morador') {
        data.id = formType === 'edit' ? $(`#editIdMorador`).val() : undefined;
        data.nome = $(`#${formType === 'edit' ? 'editNome' : 'nome'}`).val();
        data.cpf = $(`#${formType === 'edit' ? 'editCpf' : 'cpf'}`).val();
        data.telefone = $(`#${formType === 'edit' ? 'editTelefone' : 'telefone'}`).val();
    }

    if (tableType === 'predios') {
        data.id = formType === 'edit' ? $(`#editIdPredios`).val() : undefined;
        data.nome = $(`#${formType === 'edit' ? 'editNome' : 'nome'}`).val();
        data.cep = $(`#${formType === 'edit' ? 'editCep' : 'cep'}`).val();
        data.cidade = $(`#${formType === 'edit' ? 'editCidade' : 'cidade'}`).val();
        data.bairro = $(`#${formType === 'edit' ? 'editBairro' : 'bairro'}`).val();
        data.rua = $(`#${formType === 'edit' ? 'editRua' : 'rua'}`).val();
        data.numero = $(`#${formType === 'edit' ? 'editNumero' : 'numero'}`).val();
        data.id_sindico = $(`#${formType === 'edit' ? 'editIdSindico' : 'idSindico'}`).val();

    }

    if (tableType === 'despesas') {
        data.id = formType === 'edit' ? $(`#editIdDespesas`).val() : undefined;
        data.id_predio = $(`#${formType === 'edit' ? 'editIdPredio' : 'idPredio'}`).val();
        data.id_contato = $(`#${formType === 'edit' ? 'editIdContato' : 'idContato'}`).val();
        data.nome = $(`#${formType === 'edit' ? 'editNome' : 'nome'}`).val();

        let valorTotal = $(`#${formType === 'edit' ? 'editValor-total' : 'valor-total'}`).val()
            .replace('R$', '')
            .replace(/\./g, '')
            .replace(',', '.')
            .trim();

        let valorNumerico = parseFloat(valorTotal) || 0;
        data.valor_total = valorNumerico;

        const vencimento = $(`#${formType === 'edit' ? 'editVencimento' : 'vencimento'}`).val();
        console.log('Vencimento original:', vencimento);
        data.vencimento = formatarData(vencimento);
        console.log('Vencimento formatado:', data.vencimento);
    }


    if (tableType === 'contatos') {

        data.id = formType === 'edit' ? $(`#editIdContatos`).val() : undefined;
        data.id_predio = $(`#${formType === 'edit' ? 'editIdPredio' : 'idPredio'}`).val();
        data.nome = $(`#${formType === 'edit' ? 'editNome' : 'nome'}`).val();
        data.telefone = formatarTelefone($(`#${formType === 'edit' ? 'editTelefone' : 'telefone'}`).val());
        data.cep = $(`#${formType === 'edit' ? 'editCep' : 'cep'}`).val();
        data.cidade = $(`#${formType === 'edit' ? 'editCidade' : 'cidade'}`).val();
        data.bairro = $(`#${formType === 'edit' ? 'editBairro' : 'bairro'}`).val();
        data.rua = $(`#${formType === 'edit' ? 'editRua' : 'rua'}`).val();
        data.numero = $(`#${formType === 'edit' ? 'editNumero' : 'numero'}`).val();
    }

    if (tableType === 'apartamentos') {
        data.id = formType === 'edit' ? $(`#editIdApartamentos`).val() : undefined;
        data.id_predio = $(`#${formType === 'edit' ? 'editIdPredio' : 'idPredio'}`).val();
        data.id_morador = $(`#${formType === 'edit' ? 'editIdMorador' : 'idMorador'}`).val();
        data.numero = $(`#${formType === 'edit' ? 'editNumero' : 'numero'}`).val();
        data.andar = $(`#${formType === 'edit' ? 'editAndar' : 'andar'}`).val();
    }
    if (tableType === 'despesa_apartamento') {
        data.id_apartamento = $(`#idApartamento`).val();
        data.id = $(`#editIdDespesa_Apartamento`).val();
        data.nome = $(`#${formType === 'edit' ? 'editNome' : 'nome'}`).val();

        let valorTotal = $(`#${formType === 'edit' ? 'editValor-total' : 'valor-total'}`).val()
            .replace('R$', '')
            .replace(/\./g, '')
            .replace(',', '')
            .trim();

        let valorNumerico = parseFloat(valorTotal.slice(0, -2) + '.' + valorTotal.slice(-2)) || 0;
        data.valor_total = valorNumerico;
    }
    return data;
}

function submitForm(tableType, data, method) {
    console.log('Enviando dados para o servidor: ', data);

    $.ajax({
        url: `../php/script/script.php?table=${tableType}`,
        method: method,
        contentType: 'application/json',
        data: JSON.stringify(data),
        dataType: 'json',
        success: function (response) {
            console.log('Resposta recebida:', response);
            if (response.status === 'success') {
                console.log('Recarregando a página...');
                location.reload()
            } else {
                console.warn('Resposta inesperada:', response);
            }
        }
    });
}



$(document).ready(function () {
    hideAllPopups();
});
