<?php

include '../database/conecte.php';
include '../crud/cruddespesa.php';
include '../crud/apartamento_despesas.php';
require('../../fpdf/fpdf.php');

$data_json = file_get_contents('php://input');

if (empty($data_json)) {
    die("Dados não recebidos corretamente.");
}

$data = json_decode($data_json, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    die("Erro ao decodificar o JSON: " . json_last_error_msg());
}

class PDF extends FPDF
{
    function Header()
    {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, utf8_decode('Relatório de Cálculo da Taxa Condominial'), 0, 1, 'C');
        $this->Ln(10);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Página ' . $this->PageNo(), 0, 0, 'C');
    }
}

function taxaCondominial($valor_total, $unidade)
{
    return ($unidade > 0) ? $valor_total / $unidade : 0;
}

$id_predio = $data['id_predio'];
$mes = $data['mes'];
$ano = $data['ano'];
$reajuste = $data['inflacao'];

$meses = [
    1 => 'janeiro',
    2 => 'fevereiro',
    3 => 'março',
    4 => 'abril',
    5 => 'maio',
    6 => 'junho',
    7 => 'julho',
    8 => 'agosto',
    9 => 'setembro',
    10 => 'outubro',
    11 => 'novembro',
    12 => 'dezembro'
];

$mes_nome = isset($meses[$mes]) ? $meses[$mes] : 'mês inválido';

$valor_total = consultarValorTotalDespesasPredio($conn, $id_predio, $ano, $mes);
$quantidade_unidades = contarUnidadesPorDespesas($conn, $id_predio, $ano, $mes);

$taxa = taxaCondominial($valor_total, $quantidade_unidades);
$emergencial = $taxa * 0.10;
$nova_taxa = $taxa * (1 + ($reajuste / 100));

$nova_emergencial = $nova_taxa * 0.10;

$taxa_12meses = $nova_taxa / 12;
$taxa_emergencial = $emergencial / 12;
$emg_12meses = $taxa_emergencial + $taxa_12meses;

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 14);

$pdf->Cell(0, 5, utf8_decode("Relatório de Cálculo da Taxa Condominial"), 0, 1, 'C');
$pdf->SetFont('Arial', '', 10);
if ($mes_nome == 'mês inválido') {
    $pdf->Cell(0, 5, utf8_decode("Do ano de " . $ano), 0, 1, 'C');
    $pdf->Ln(8);
} else {
    $pdf->Cell(0, 5, utf8_decode("Do ano de " . $ano . " do mês de " . $mes_nome), 0, 1, 'C');
    $pdf->Ln(8);
}

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(95, 10, utf8_decode("Descrição"), 1, 0, 'C');
$pdf->Cell(95, 10, utf8_decode("Valor"), 1, 1, 'C');

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(95, 10, utf8_decode("Taxa Condominial (Por Apartamento)"), 1, 0, 'L');
$pdf->Cell(95, 10, "R$ " . number_format($taxa, 2, ',', '.'), 1, 1, 'R');

$pdf->Cell(95, 10, utf8_decode("Fundo Emergencial (10%)"), 1, 0, 'L');
$pdf->Cell(95, 10, "R$ " . number_format($emergencial, 2, ',', '.'), 1, 1, 'R');

$pdf->Cell(95, 10, utf8_decode("Reajuste (Inflação)"), 1, 0, 'L');
$pdf->Cell(95, 10, $reajuste . "%", 1, 1, 'R');

$pdf->Cell(95, 10, utf8_decode("Nova Taxa (Com Reajuste)"), 1, 0, 'L');
$pdf->Cell(95, 10, "R$ " . number_format($nova_taxa, 2, ',', '.'), 1, 1, 'R');

$pdf->Cell(95, 10, utf8_decode("Fundo Emergencial (Com Reajuste)"), 1, 0, 'L');
$pdf->Cell(95, 10, "R$ " . number_format($nova_emergencial, 2, ',', '.'), 1, 1, 'R');

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(95, 10, utf8_decode("Será pago pelo morador"), 1, 0, 'C');
$pdf->Cell(95, 10, utf8_decode("Valor"), 1, 1, 'C');

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(95, 10, utf8_decode("Taxa Condominial Mensal"), 1, 0, 'L');
$pdf->Cell(95, 10, "R$ " . number_format($taxa_12meses, 2, ',', '.'), 1, 1, 'R');

$pdf->Cell(95, 10, utf8_decode("Taxa Emergencial Mensal"), 1, 0, 'L');
$pdf->Cell(95, 10, "R$ " . number_format($taxa_emergencial, 2, ',', '.'), 1, 1, 'R');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(95, 10, utf8_decode("Mensalidade Final"), 1, 0, 'L');
$pdf->Cell(95, 10, "R$ " . number_format($emg_12meses, 2, ',', '.'), 1, 1, 'R');
$pdf->Ln(10);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(45, 10, utf8_decode("Despesa"), 1, 0, 'C');
$pdf->Cell(40, 10, utf8_decode("Valor"), 1, 0, 'C');
$pdf->Cell(45, 10, utf8_decode("Contato"), 1, 0, 'C');
$pdf->Cell(30, 10, utf8_decode("Vencimento"), 1, 0, 'C');
$pdf->Cell(15, 10, utf8_decode("Pago?"), 1, 1, 'C');

$despesas = getDespesasAno($conn, $mes, $ano);

usort($despesas, function($a, $b) {
    $dateA = DateTime::createFromFormat('d/m/Y', $a['vencimento']);
    $dateB = DateTime::createFromFormat('d/m/Y', $b['vencimento']);

    return $dateA <=> $dateB;
});

$pdf->SetFont('Arial', '', 12);

foreach ($despesas as $despesa) {
    $pdf->SetXY($pdf->GetX(), $pdf->GetY());
    $pdf->MultiCell(45, 10, utf8_decode($despesa['nome']), 1, 'C');

    $pdf->SetXY($pdf->GetX() + 45, $pdf->GetY() - 10);
    $pdf->Cell(40, 10, utf8_decode("R$ " . number_format($despesa['valor_total'], 2, ',', '.')), 1, 0, 'R');

    $pdf->Cell(45, 10, utf8_decode($despesa['nome_contato']), 1, 0, 'C');
    $pdf->Cell(30, 10, utf8_decode($despesa['vencimento']), 1, 0, 'C');
    $pdf->Cell(15, 10, utf8_decode($despesa['foi_pago']), 1, 1, 'C');
}



$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(55, 10, utf8_decode("Valor Total das Despesas"), 1, 0, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(35, 10, utf8_decode("R$ " . number_format($valor_total, 2, ',', '.')), 1, 1, 'R');

$nome_arquivo = 'relatorio_predio_' . $id_predio . '_' . date('Y-m-d') . '.pdf';

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $nome_arquivo . '"');
$pdf->Output();
?>