<?php
require_once 'vendor/autoload.php';
session_start();

// Verifica se o arquivo recebido é um pdf
if($_FILES['pdf_file']['type'] != "application/pdf"){
    $_SESSION['erro'] = "O arquivo enviado não é um PDF";
    header("Location: expedicao_vasp.php");
    exit();
}

$origem = $_POST['origem'] ?? 'email';
$parser = new \Smalot\PdfParser\Parser();
$pdf = $parser->parseFile($_FILES["pdf_file"]["tmp_name"]);


if($origem == 'email'){
    $pageData = $pdf->getPages();
    $pageData = $pageData[0]->getDataTm();

    // Identificar os limites inferiores e superiores da tabela
    // Redirecionar com erro caso não tenha tabela
    $limitesTabela = limitesTabela($pageData);
    if(empty($limitesTabela)){
        $_SESSION['erro'] = "Tabela não encontrada";
        header("Location: expedicao_vasp.php");
        exit();
    }

    // Extrair a tabela do resto do PDF
    $tabela = extrairTabela($pageData, $limitesTabela);

    // Extrair os dados da tabela
    $dados = extrairDados($tabela);
} else {
    // Da Plataforma
    $tabelaPorData = extrairTabelaPlataforma($pdf);
    
    if(empty($tabelaPorData)){
        $_SESSION['erro'] = "Tabela não encontrada na Plataforma";
        header("Location: expedicao_vasp.php");
        exit();
    }
    
    $dados = extrairDadosPlataforma($tabelaPorData);
}

// Retorna a tabela pela sessão
$_SESSION['dados'] = $dados;
$_SESSION['upload_feito'] = true;
header("Location: expedicao_vasp.php");
exit();


function extrairDados($tabela, $data_distribuicao = ''){
    $dados = [];
    $i = 1; // Identificador unico de cada item
    // Percorre cada linha da tabela
    foreach($tabela as $linha){
        // Dados que não mudam
        $iva = 0.06;
        $tem_stock = 1;
        $categoria = "VASP";
        $tipo_artigo = "Produto";
        $inventario_existencia = "Mercadorias";
        $un_medida = "Unidade";
        $fornecedor = 81;

        // Inicializacao de variaveis
        $ean = "";
        $artigo = "";
        $preco = 0;
        $preco_com_iva = 0;
        $descricao = "";
        $pvp = 0;
        $pvp_sIva = 0;
        $quantidade = 0;

        // verifica cada elemento da linha e extrai os dados necessários
        foreach($linha as $elemento){
            $x = floatval($elemento['x']);
            
            if ($x >= 25 && $x < 60) {
                $artigo = trim($elemento['conteudo']);
            }
            else if ($x >= 60 && $x < 230) {
                // Caso a descrição venha partida em 2 blocos, concatenamos
                $descricao .= " " . trim($elemento['conteudo']);
                $descricao = trim($descricao);
            }
            else if ($x >= 230 && $x < 290) {
                // Preço Custo
                $clean_val = str_replace(['€', ' '], '', $elemento['conteudo']);
                $val = floatval(str_replace(",", ".", $clean_val));
                
                if (is_numeric(str_replace(",", ".", $clean_val))) {
                    $preco_com_iva = $val;
                    $preco = number_format($preco_com_iva/(1+$iva), 5, '.', '');
                }
            }
            else if ($x >= 290 && $x < 340) {
                // PVP
                $clean_val = str_replace(['€', ' '], '', $elemento['conteudo']);
                $val = floatval(str_replace(",", ".", $clean_val));
                
                if (is_numeric(str_replace(",", ".", $clean_val))) {
                    $pvp = $val;
                    $pvp_sIva = number_format($pvp/(1+$iva), 5, '.', '');
                }
            }
            else if ($x >= 400 && $x < 460) {
                // EAN
                $clean_val = trim($elemento['conteudo']);
                if(is_numeric($clean_val) && strlen($clean_val) > 5) {
                    $ean = $clean_val;
                }
            }
            else if ($x >= 520 && $x < 590) {
                // Quantidade
                $val = (int) trim($elemento['conteudo']);
                if ($val > 0) {
                    $quantidade = $val;
                }
            }
        }
        
        // Ignorar se a linha não teve um artigo válido extraído (linha em branco/lixo)
        if(empty($artigo)) continue;
        
        $dados["{$i}"] = ['artigo'=>$artigo,
                    'iva'=> $iva,
                    'descricao'=>$descricao, 
                    'preco'=>$preco,
                    'preco_com_iva' => $preco_com_iva, 
                    'pvp'=>$pvp,
                    'pvp_sIva'=>$pvp_sIva, 
                    'ean'=>$ean, 
                    'stock'=>$quantidade,
                    'tem_stock'=>$tem_stock,
                    'categoria'=>$categoria,
                    'tipo_artigo'=>$tipo_artigo,
                    'inventario_existencia'=>$inventario_existencia,
                    'un_medida'=>$un_medida,
                    'fornecedor'=>$fornecedor,
                    'data_distribuicao' => $data_distribuicao
                ];
        $i++;
    }

    return $dados;
}

function extrairTabelaPlataforma($pdf){
    $tabela_por_data = [];
    $lendo_tabela = false;
    $data_atual = "";
    
    $pages = $pdf->getPages();
    foreach($pages as $page){
        $pageData = $page->getDataTm();
        
        $linhas_da_pagina = [];
        $ultimo_y = 0;
        
        // 1. Agrupar os elementos pela coordenada Y (linha)
        foreach($pageData as $elemento){
            $y = (float) $elemento[0][5];
            $x = $elemento[0][4];
            $conteudo = $elemento[1];

            // Juntar textos com quebra de linha
            if($ultimo_y - $y < 9 && $ultimo_y != 0 && $ultimo_y - $y > 0){
                foreach($linhas_da_pagina[(string)$ultimo_y] as &$linha_el){
                    if($linha_el['x'] == $x){
                        $linha_el['conteudo'] .= $conteudo;
                    }
                }
                continue;
            }
            
            $y_str = (string)$y;
            if(!isset($linhas_da_pagina[$y_str])){
                $linhas_da_pagina[$y_str] = [];
            }
            $linhas_da_pagina[$y_str][] = ['x'=>$x, 'conteudo'=>$conteudo];
            $ultimo_y = $y;
        }

        // 2. Analisar linha a linha
        foreach($linhas_da_pagina as $y_str => $elementos){
            // Criar uma string de texto corrido para verificar a linha toda
            $linha_texto_completo = "";
            foreach($elementos as $el){
                $linha_texto_completo .= trim($el['conteudo']) . " ";
            }
            $linha_texto_completo = trim($linha_texto_completo);

            if(str_contains($linha_texto_completo, "Totais por Artigo:")){
                $lendo_tabela = true;
                continue;
            }
            
            if(str_contains($linha_texto_completo, "Detalhe por Guia:")){
                $lendo_tabela = false;
                break 2; // Para completamente a pesquisa em todas as páginas
            }
            
            if($lendo_tabela){
                if(str_contains($linha_texto_completo, "Data Distribuição:")){
                    // Extrair a data (o que estiver à frente dos dois pontos)
                    $partes = explode("Data Distribuição:", $linha_texto_completo);
                    $data_bruta = trim($partes[1]);
                    // Garantir que apanhamos só a data (YYYY-MM-DD)
                    $tokens = explode(" ", $data_bruta);
                    $data_atual = trim($tokens[0]);
                    continue;
                }
                
                // Ignorar cabeçalhos de tabela
                if(str_contains($linha_texto_completo, "Artigo") && str_contains($linha_texto_completo, "Descrição")){
                    continue;
                }
                
                // Ignorar rodapés de página (Impressão: Data Hora)
                if(str_contains($linha_texto_completo, "Impressão:")){
                    continue;
                }

                if($data_atual != ""){
                    if(!isset($tabela_por_data[$data_atual])){
                        $tabela_por_data[$data_atual] = [];
                    }
                    $tabela_por_data[$data_atual][] = $elementos;
                }
            }
        }
    }

    return $tabela_por_data;
}


function extrairDadosPlataforma($tabelaPorData){
    $dados = [];
    $idGeral = 1; 
    foreach($tabelaPorData as $data_dist => $linhas){
        // Usar a mesma função extrairDados, que devolve as linhas com id sequencial
        $dadosData = extrairDados($linhas, $data_dist);
        foreach($dadosData as $artigoObj){
            $dados[$idGeral] = $artigoObj;
            $idGeral++;
        }
    }
    return $dados;
}


function extrairTabela($pageData, $limitesTabela){
    $tabela = [];

    $ultimo_y = 0;
    foreach($pageData as $elemento){
        $y = (float) $elemento[0][5];

        $limiteFim = $limitesTabela['fim'] ?? 0;
        
        if($y <= $limitesTabela['comeco'] && $y >= $limiteFim){
            
            $x = $elemento[0][4];
            $conteudo = $elemento[1];


            // Compara com o Y da linha anterior, pra verificar se é um outro artigo, ou quebra de linha do mesmo
            if($ultimo_y - $y < 9 && $ultimo_y != 0 && $ultimo_y - $y > 0){
                foreach($tabela[$ultimo_y] as &$linha){
                    if($linha['x'] == $x){
                        $linha['conteudo'] .= $conteudo;
                    }
                }
                continue;
            }
        

            if(!isset($tabela[$y])){
                $tabela[$y] = [];
            }
                    
            $tabela[$y][] = ['x'=>$x, 'conteudo'=>$conteudo];
            }
            $ultimo_y = $y;
    }

    // Elimina título e cabeçalho da tabela
    array_shift($tabela);
    array_shift($tabela);
    array_shift($tabela);

    return $tabela;
}



function limitesTabela($pageData){
    $limitesTabela = [];
    foreach($pageData as $elemento){
        if($elemento[1] == "Totais por Artigo:"){
            $limitesTabela['comeco'] = (float) $elemento[0][5];
        }
    
        if($elemento[1] == "Detalhe por Guia:"){
            $limitesTabela['fim'] = (float) $elemento[0][5];
        }
    }

    return $limitesTabela;
}
?>