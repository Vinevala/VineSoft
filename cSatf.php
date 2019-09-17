<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FaturasC
 *
 * @author VINEVALA
 */
class cSatf extends CI_Controller {
     public function calcuralCustoReal($desconto,$valor){
         if($desconto>0){
         $percentagem=  $valor*$desconto/100;
         $preco=$valor-$percentagem;
         return $preco;
         } else {
           return $valor;  
         }
    }
    public function acharTotal($desconto,$qtd,$valor){
     if($desconto>0){
        $desconto1=($valor*$desconto)/100;
        $preco=$valor-$desconto1;
       return $total=$preco*$qtd;
     } else {
          return $total=$valor*$qtd;
     }
    }
    public function calcuralDesconto($desconto,$qtd,$valor){
        $desconto1=($valor*$desconto)/100;
        return $desconto1*$qtd;
    }
    public function tipoVenda($idtipo){
          if($idtipo==1){
                  return  'FT';
                }else{
                  return 'VD';     
           }
    }
     public function verifica_tipo($tipo){// 
           if($tipo=='A'){
              return 'P'; 
           }else{
              return 'S'; 
           }
          
     }
     public function verifica_categoria($idCategoria){// 
           if($idCategoria==''){
              return 'Desconhecido'; 
           }else{
              return $this->FaturasM->pegaCategoria($idCategoria); 
           }
          
     }public function pega_as_notas_credito($xml){// pega as notas de credito
           $dataminima='';
           $datamaxima='';// 
           $id_usiario=3;
           $root = $xml->createElement("SourceDocuments");
           
           
           $ntaCredito=$this->FaturasM->mNotaCredito->notaCreditoSaft($dataminima,$datamaxima);// pega as todas as faturas para o saft
           foreach ($ntaCredito as $linha) {// saftxml_produtos
                   
                   $idFatuta=$linha->fatura;  
                   $ultimaDataAlteracao= $this->mNotaCredito->liastaDataNotaCredito($idFatuta);
                   $tipoVenda = $this->tipoVenda($linha->idtipo) ;
                   $data =$linha->data;
                   $sd3= new DateTime($data);
                   $dtv=$sd3->format('d-m-Y');
                   $ano=$sd3->format('Y');
                   $Invoice= $xml->createElement("Invoice");
                   $InvoiceNo= $xml->createElement("InvoiceNo",$tipoVenda.' '.$ano.'/'. $idFatuta); 
                   $DocumentStatus= $xml->createElement("DocumentStatus"); 
                   $InvoiceStatus= $xml->createElement("InvoiceStatus","N"); 
                   $InvoiceStatusDate= $xml->createElement("InvoiceStatusDate", $linha->estado == 1 ? $data.'T'.$linha->hora : $ultimaDataAlteracao); 
                   $Reason= $xml->createElement("Reason", strlen($ultimaDataAlteracao)> 1 ? "Devolução de Mercadoria" : ""); 
                   $SourceID= $xml->createElement("SourceID",$id_usiario);
                   $SourceBilling= $xml->createElement("SourceBilling","P");
                  
                   //Inserir fihos a documetos status
                   $DocumentStatus->appendChild($InvoiceStatus);
                   $DocumentStatus->appendChild($InvoiceStatusDate);
                   $DocumentStatus->appendChild($Reason);
                   $DocumentStatus->appendChild($SourceID);
                   $DocumentStatus->appendChild($SourceBilling); 
                   //
                    $Hash= $xml->createElement("Hash",$linha->hash);
                    $HashControl= $xml->createElement("HashControl",$linha->hashcontrol);//
                    $Period= $xml->createElement("Period",4);
                    $InvoiceDate= $xml->createElement("InvoiceDate",$data);
                    $InvoiceType= $xml->createElement("InvoiceType","NC");
                    $SpecialRegimes= $xml->createElement("SpecialRegimes");
                    // ADICIONAR NOS AO SpecialRegimes
                      $SelfBillingIndicator= $xml->createElement("SelfBillingIndicator",0);
                      $CashVATSchemeIndicator= $xml->createElement("CashVATSchemeIndicator",0);
                      $ThirdPartiesBillingIndicator= $xml->createElement("ThirdPartiesBillingIndicator",0);
                      $SpecialRegimes->appendChild($SelfBillingIndicator);
                      $SpecialRegimes->appendChild($CashVATSchemeIndicator);
                      $SpecialRegimes->appendChild($ThirdPartiesBillingIndicator);
                    //
                     $SourceID1= $xml->createElement("SourceID",$id_usiario);
                     $EACCode= $xml->createElement("EACCode","47411"); 
                     $SystemEntryDate= $xml->createElement("SystemEntryDate", @date('Y-m-d').'T'.@date('H:i:s'));
                     $CustomerID= $xml->createElement("CustomerID", $linha->idcliente);
                    //
                     //Criar a tag chipTo e seus filhos
                    $ShipTo= $xml->createElement("ShipTo");
                    $Address= $xml->createElement("Address");
                    $AddressDetail= $xml->createElement("AddressDetail","Morada do Armazem ");
                    $City= $xml->createElement("City","Huambo");
                    $Country= $xml->createElement("Country","AO");
                    $Address->appendChild($AddressDetail);
                    $Address->appendChild($City);
                    $Address->appendChild($Country);
                    $ShipTo->appendChild($Address);
                    //Criar a tag ShipFrom e seus filhos
                    $SShipFrom= $xml->createElement("ShipFrom");
                    $Address1= $xml->createElement("Address");
                    $AddressDetail1= $xml->createElement("AddressDetail","Morada do Cliente");
                    $City1= $xml->createElement("City","Huambo");
                    $Country1= $xml->createElement("Country","AO");
                    $Address1->appendChild($AddressDetail1);
                    $Address1->appendChild($City1);
                    $Address1->appendChild($Country1);
                    $SShipFrom->appendChild($Address1);
                    //
                    
                    //
                    $Invoice->appendChild($InvoiceNo);
                    $Invoice->appendChild($DocumentStatus);
                    $Invoice->appendChild($Hash);
                    $Invoice->appendChild($HashControl);
                    $Invoice->appendChild($Period);
                    $Invoice->appendChild($InvoiceDate);
                    $Invoice->appendChild($InvoiceType);
                    $Invoice->appendChild($SpecialRegimes);
                    $Invoice->appendChild($SourceID1);
                    $Invoice->appendChild($EACCode);
                    $Invoice->appendChild($SystemEntryDate);
                    $Invoice->appendChild($CustomerID);
                    $Invoice->appendChild($ShipTo);
                    $Invoice->appendChild($SShipFrom);
                    $cont=0;
                    foreach ($this->FaturasM->produtosSaft($idFatuta) as $linha){
                         $cont++;
                             $idProduto=$linha->idpr;  
                             $Line= $xml->createElement("Line"); 
                             $LineNumber= $xml->createElement("LineNumber",$cont);
                             $ProductCode= $xml->createElement("ProductCode",$idProduto);
                             $ProductDescription= $xml->createElement("ProductDescription",$linha->produto);
                             $Quantity= $xml->createElement("Quantity",$linha->qtd);
                             $UnitOfMeasure= $xml->createElement("UnitOfMeasure","Unidade");
                             $UnitPrice= $xml->createElement("UnitPrice",$linha->custo);
                             $TaxPointDate= $xml->createElement("TaxPointDate",$linha->datavenda);
                             $Description= $xml->createElement("Description",$linha->produto);
                             $DebitAmount= $xml->createElement("DebitAmount",$linha->total);
                             $Line->appendChild($LineNumber);
                             $Line->appendChild($ProductCode);
                             $Line->appendChild($ProductDescription);
                             $Line->appendChild($Quantity);
                             $Line->appendChild($UnitOfMeasure);
                             $Line->appendChild($UnitPrice);
                             $Line->appendChild($TaxPointDate);
                             $Line->appendChild($Description);
                             $Line->appendChild($DebitAmount);
                             $Tax= $xml->createElement("Tax");
                             $TaxType= $xml->createElement("TaxType","IVA");
                             $TaxCountryRegion= $xml->createElement("TaxCountryRegion","AO");
                             $TaxCode= $xml->createElement("TaxCode","NOR");
                             $TaxPercentage= $xml->createElement("TaxPercentage",14.00);
                             $Tax->appendChild($TaxType);
                             $Tax->appendChild($TaxCountryRegion);
                             $Tax->appendChild($TaxCode);
                             $Tax->appendChild($TaxPercentage);
                             $Line->appendChild($Tax);
                             //
                             $Invoice->appendChild($Line);
                            
                    }

                    $Invoice->appendChild($DocumentTotals);
                  
                    $root->appendChild($SalesInvoices);
            }
          return  $xml->appendChild($root);
     }
     public function pega_as_faturas_normas_e_anuladas($xml){// tag que contem infotmacao dos clientes e produtos
           $dataminima='';
           $datamaxima='';// 
           $id_usiario=3;
           $totalDebito= $this->FaturasM->somarVendasDebito($dataminima,$datamaxima);
           $totalCredito=$this->FaturasM->somarVendas($dataminima,$datamaxima);
           $SalesInvoices= $xml->createElement("SalesInvoices"); 
           $ntCr=  count($this->FaturasM->mNotaCredito->notaCreditoSaft($dataminima,$datamaxima));
           $NumberOfEntries = $xml->createElement("NumberOfEntries", count($this->FaturasM->saftxml_faturas_contar($dataminima,$datamaxima))+$ntCr); 
           $TotalDebit = $xml->createElement("TotalDebit",$totalDebito); 
           $TotalCredit = $xml->createElement("TotalCredit", $totalCredito); 
           $SalesInvoices->appendChild($NumberOfEntries);
           $SalesInvoices->appendChild($TotalDebit);
           $SalesInvoices->appendChild($TotalCredit);
           // imprime nota de credito
           
           $ntaCredito=$this->FaturasM->mNotaCredito->notaCreditoSaft($dataminima,$datamaxima);// pega as todas as faturas para o saft
           foreach ($ntaCredito as $linha) {// saftxml_produtos
                    $ivafornecedor=0;
                    $totalIvaFornecedor=0;
                    $totalDescontos=0;
                    $subtotal=0;
                    $totalIva=0;
                   $idFatuta=$linha->fatura; 
                   $ultimaDataAlteracao= $this->mNotaCredito->liastaDataNotaCredito($idFatuta);
                   $tipoVenda = $this->tipoVenda($linha->idtipo) ;
                   $data =$linha->data;
                   $sd3= new DateTime($data);
                   $dtv=$sd3->format('d-m-Y');
                   $ano=$sd3->format('Y');
                   $Invoice= $xml->createElement("Invoice");
                   $InvoiceNo= $xml->createElement("InvoiceNo","NC".' '.$ano.'/'. $idFatuta); 
                   $DocumentStatus= $xml->createElement("DocumentStatus"); 
                   $InvoiceStatus= $xml->createElement("InvoiceStatus","N"); 
                   $InvoiceStatusDate= $xml->createElement("InvoiceStatusDate",  $data.'T'.$linha->hora ); 
                   $Reason= $xml->createElement("Reason",  "Devolução de Mercadoria"); 
                   $SourceID= $xml->createElement("SourceID",$id_usiario);
                   $SourceBilling= $xml->createElement("SourceBilling","P");
                  
                   //Inserir fihos a documetos status
                   $DocumentStatus->appendChild($InvoiceStatus);
                   $DocumentStatus->appendChild($InvoiceStatusDate);
                   $DocumentStatus->appendChild($Reason);
                   $DocumentStatus->appendChild($SourceID);
                   $DocumentStatus->appendChild($SourceBilling); 
                   //
                    $Hash= $xml->createElement("Hash",$linha->hash);
                    $HashControl= $xml->createElement("HashControl",$linha->hashcontrol);//
                    $Period= $xml->createElement("Period",4);
                    $InvoiceDate= $xml->createElement("InvoiceDate",$data);
                    $InvoiceType= $xml->createElement("InvoiceType","NC");
                    $SpecialRegimes= $xml->createElement("SpecialRegimes");
                    // ADICIONAR NOS AO SpecialRegimes
                      $SelfBillingIndicator= $xml->createElement("SelfBillingIndicator",0);
                      $CashVATSchemeIndicator= $xml->createElement("CashVATSchemeIndicator",0);
                      $ThirdPartiesBillingIndicator= $xml->createElement("ThirdPartiesBillingIndicator",0);
                      $SpecialRegimes->appendChild($SelfBillingIndicator);
                      $SpecialRegimes->appendChild($CashVATSchemeIndicator);
                      $SpecialRegimes->appendChild($ThirdPartiesBillingIndicator);
                    //
                     $SourceID1= $xml->createElement("SourceID",$id_usiario);
                     $EACCode= $xml->createElement("EACCode","47411"); 
                     $SystemEntryDate= $xml->createElement("SystemEntryDate", @date('Y-m-d').'T'.@date('H:i:s'));
                     $CustomerID= $xml->createElement("CustomerID", $linha->idcliente);
                    //
                     //Criar a tag chipTo e seus filhos
                    $ShipTo= $xml->createElement("ShipTo");
                    $Address= $xml->createElement("Address");
                    $AddressDetail= $xml->createElement("AddressDetail","Morada do Armazem ");
                    $City= $xml->createElement("City","Huambo");
                    $Country= $xml->createElement("Country","AO");
                    $Address->appendChild($AddressDetail);
                    $Address->appendChild($City);
                    $Address->appendChild($Country);
                    $ShipTo->appendChild($Address);
                    //Criar a tag ShipFrom e seus filhos
                    $SShipFrom= $xml->createElement("ShipFrom");
                    $Address1= $xml->createElement("Address");
                    $AddressDetail1= $xml->createElement("AddressDetail","Morada do Cliente");
                    $City1= $xml->createElement("City","Huambo");
                    $Country1= $xml->createElement("Country","AO");
                    $Address1->appendChild($AddressDetail1);
                    $Address1->appendChild($City1);
                    $Address1->appendChild($Country1);
                    $SShipFrom->appendChild($Address1);
                    //
                    
                    //
                    $Invoice->appendChild($InvoiceNo);
                    $Invoice->appendChild($DocumentStatus);
                    $Invoice->appendChild($Hash);
                    $Invoice->appendChild($HashControl);
                    $Invoice->appendChild($Period);
                    $Invoice->appendChild($InvoiceDate);
                    $Invoice->appendChild($InvoiceType);
                    $Invoice->appendChild($SpecialRegimes);
                    $Invoice->appendChild($SourceID1);
                    $Invoice->appendChild($EACCode);
                    $Invoice->appendChild($SystemEntryDate);
                    $Invoice->appendChild($CustomerID);
                    $Invoice->appendChild($ShipTo);
                    $Invoice->appendChild($SShipFrom);
                    $cont=0;
                    foreach ($this->FaturasM->produtosSaft($idFatuta) as $linha){
                         $cont++;
                         
                             $idProduto=$linha->idpr;  
                             $Line= $xml->createElement("Line"); 
                             $LineNumber= $xml->createElement("LineNumber",$cont);
                             $ProductCode= $xml->createElement("ProductCode",$idProduto);
                             $ProductDescription= $xml->createElement("ProductDescription",$linha->produto);
                             $Quantity= $xml->createElement("Quantity",$linha->qtd);
                             $UnitOfMeasure= $xml->createElement("UnitOfMeasure","Unidade");
                             $UnitPrice= $xml->createElement("UnitPrice",$this->calcuralCustoReal($linha->desconto,$linha->custo));
                             $TaxPointDate= $xml->createElement("TaxPointDate",$linha->datavenda);
                             $Description= $xml->createElement("Description",$linha->produto);
                             $DebitAmount= $xml->createElement("DebitAmount",$linha->total);
                             $Line->appendChild($LineNumber);
                             $Line->appendChild($ProductCode);
                             $Line->appendChild($ProductDescription);
                             $Line->appendChild($Quantity);
                             $Line->appendChild($UnitOfMeasure);
                             $Line->appendChild($UnitPrice);
                             $Line->appendChild($TaxPointDate);
                             $Line->appendChild($Description);
                             $Line->appendChild($DebitAmount);
                             $Tax= $xml->createElement("Tax");
                             $excepcao=$this->mIva->pega_exepcao($idProduto);
                             $TaxType= $xml->createElement("TaxType","IVA");
                             $TaxCountryRegion= $xml->createElement("TaxCountryRegion","AO");
                             $TaxCode= $xml->createElement("TaxCode", strlen($excepcao)>1 ? "ISE" : "NOR");
                             $TaxPercentage= $xml->createElement("TaxPercentage", strlen($excepcao)>1 ? 0 : 14.00);
                             $TaxExemptionReason= $xml->createElement("TaxExemptionReason",$this->ProdutosM->mpega_exepcao_rasao($idProduto));
                             $TaxExemptionCode= $xml->createElement("TaxExemptionCode",$excepcao);
                             $Tax->appendChild($TaxType);
                             $Tax->appendChild($TaxCountryRegion);
                             $Tax->appendChild($TaxCode);
                             $Tax->appendChild($TaxPercentage);
                             $Line->appendChild($Tax);
                             if(strlen($excepcao)>1){
                              $Line->appendChild($TaxExemptionReason);
                              $Line->appendChild($TaxExemptionCode); 
                             }
                             //
                             $Invoice->appendChild($Line);
                             $ivafornecedor=$this->mIva->iva_fornecedor($idProduto);
                             $totalIvaFornecedor=$totalIvaFornecedor+$ivafornecedor;
                             $desconto = $this->calcuralDesconto($linha->desconto,$linha->qtd,$linha->custo);
                             
                             $totalDescontos=$totalDescontos+$desconto;
                             $subtotal=$subtotal+ $total = $linha->total;
                             $ivaInt=$this->mIva->calculo_iva_int($desconto+$total,$idProduto);
                             $totalIva=$totalIva+$ivaInt;
                            
                    }
                    $totalLiquido= $this->FaturasM->somarSubTotalLiquido($idFatuta);
                     $totalGroso= $totalIva+$totalLiquido; 
                     $DocumentTotals= $xml->createElement("DocumentTotals");
                     $TaxPayable= $xml->createElement("TaxPayable",$totalIva);
                     $NetTotal= $xml->createElement("NetTotal",$totalLiquido);
                     $GrossTotal= $xml->createElement("GrossTotal",$totalGroso);
                     $DeductiblePercentage= $xml->createElement("DeductiblePercentage",14);
                     $DocumentTotals->appendChild($TaxPayable);
                     $DocumentTotals->appendChild($NetTotal);
                     $DocumentTotals->appendChild($GrossTotal);
                     $DocumentTotals->appendChild($DeductiblePercentage);
                    //
                    $Invoice->appendChild($DocumentTotals);
                    $SalesInvoices->appendChild($Invoice);
             }
           //
           
           $impostos=$this->FaturasM->saftxml_faturas($dataminima,$datamaxima);// pega as todas as faturas para o saft
           foreach ($impostos as $linha) {// saftxml_produtos
                    $ivafornecedor=0;
                    $totalIvaFornecedor=0;
                    $totalDescontos=0;
                    $subtotal=0;
                    $totalIva=0;
              
                   $idFatuta=$linha->fatura;  
                   $ultimaDataAlteracao= $this->mNotaCredito->liastaDataNotaCredito($idFatuta);
                   $tipoVenda = $this->tipoVenda($linha->idtipo) ;
                   $data =$linha->data;
                   $sd3= new DateTime($data);
                   $dtv=$sd3->format('d-m-Y');
                   $ano=$sd3->format('Y');
                   $Invoice= $xml->createElement("Invoice");
                   $InvoiceNo= $xml->createElement("InvoiceNo",$tipoVenda.' '.$ano.'/'. $idFatuta); 
                   $DocumentStatus= $xml->createElement("DocumentStatus"); 
                   $InvoiceStatus= $xml->createElement("InvoiceStatus",$linha->estado == 1 ? "N" : "A"); 
                   $InvoiceStatusDate= $xml->createElement("InvoiceStatusDate", $data.'T'.$linha->hora ); 
                   $SourceID= $xml->createElement("SourceID",$id_usiario);
                   $SourceBilling= $xml->createElement("SourceBilling","P");
                  
                   //Inserir fihos a documetos status
                   $DocumentStatus->appendChild($InvoiceStatus);
                   $DocumentStatus->appendChild($InvoiceStatusDate);
                   $DocumentStatus->appendChild($Reason);
                   $DocumentStatus->appendChild($SourceID);
                   $DocumentStatus->appendChild($SourceBilling); 
                   //
                    $Hash= $xml->createElement("Hash",$linha->hash);
                    $HashControl= $xml->createElement("HashControl",$linha->hashcontrol);//
                    $Period= $xml->createElement("Period",4);
                    $InvoiceDate= $xml->createElement("InvoiceDate",$data);
                    $InvoiceType= $xml->createElement("InvoiceType",$tipoVenda);
                    $SpecialRegimes= $xml->createElement("SpecialRegimes");
                    // ADICIONAR NOS AO SpecialRegimes
                      $SelfBillingIndicator= $xml->createElement("SelfBillingIndicator",0);
                      $CashVATSchemeIndicator= $xml->createElement("CashVATSchemeIndicator",0);
                      $ThirdPartiesBillingIndicator= $xml->createElement("ThirdPartiesBillingIndicator",0);
                      $SpecialRegimes->appendChild($SelfBillingIndicator);
                      $SpecialRegimes->appendChild($CashVATSchemeIndicator);
                      $SpecialRegimes->appendChild($ThirdPartiesBillingIndicator);
                    //
                     $SourceID1= $xml->createElement("SourceID",$id_usiario);
                     $EACCode= $xml->createElement("EACCode","47411"); 
                     $SystemEntryDate= $xml->createElement("SystemEntryDate", @date('Y-m-d').'T'.@date('H:i:s'));
                     $CustomerID= $xml->createElement("CustomerID", $linha->idcliente);
                    //
                     //Criar a tag chipTo e seus filhos
                    $ShipTo= $xml->createElement("ShipTo");
                    $Address= $xml->createElement("Address");
                    $AddressDetail= $xml->createElement("AddressDetail","Morada do Armazem ");
                    $City= $xml->createElement("City","Huambo");
                    $Country= $xml->createElement("Country","AO");
                    $Address->appendChild($AddressDetail);
                    $Address->appendChild($City);
                    $Address->appendChild($Country);
                    $ShipTo->appendChild($Address);
                    //Criar a tag ShipFrom e seus filhos
                    $SShipFrom= $xml->createElement("ShipFrom");
                    $Address1= $xml->createElement("Address");
                    $AddressDetail1= $xml->createElement("AddressDetail","Morada do Cliente");
                    $City1= $xml->createElement("City","Huambo");
                    $Country1= $xml->createElement("Country","AO");
                    $Address1->appendChild($AddressDetail1);
                    $Address1->appendChild($City1);
                    $Address1->appendChild($Country1);
                    $SShipFrom->appendChild($Address1);
                    //
                    
                    //
                    $Invoice->appendChild($InvoiceNo);
                    $Invoice->appendChild($DocumentStatus);
                    $Invoice->appendChild($Hash);
                    $Invoice->appendChild($HashControl);
                    $Invoice->appendChild($Period);
                    $Invoice->appendChild($InvoiceDate);
                    $Invoice->appendChild($InvoiceType);
                    $Invoice->appendChild($SpecialRegimes);
                    $Invoice->appendChild($SourceID1);
                    $Invoice->appendChild($EACCode);
                    $Invoice->appendChild($SystemEntryDate);
                    $Invoice->appendChild($CustomerID);
                    $Invoice->appendChild($ShipTo);
                    $Invoice->appendChild($SShipFrom);
                    $cont=0;
                    foreach ($this->FaturasM->produtosSaft($idFatuta) as $linha){
                         $cont++;
                             $idProduto=$linha->idpr;  
                             $Line= $xml->createElement("Line"); 
                             $LineNumber= $xml->createElement("LineNumber",$cont);
                             $ProductCode= $xml->createElement("ProductCode",$idProduto);
                             $ProductDescription= $xml->createElement("ProductDescription",$linha->produto);
                             $Quantity= $xml->createElement("Quantity",$linha->qtd);
                             $UnitOfMeasure= $xml->createElement("UnitOfMeasure","Unidade");
                             $UnitPrice= $xml->createElement("UnitPrice", $this->calcuralCustoReal($linha->desconto,$linha->custo));
                             $TaxPointDate= $xml->createElement("TaxPointDate",$linha->datavenda);
                             $Description= $xml->createElement("Description",$linha->produto);
                             $CreditAmount= $xml->createElement("CreditAmount",$linha->total);
                             $Line->appendChild($LineNumber);
                             $Line->appendChild($ProductCode);
                             $Line->appendChild($ProductDescription);
                             $Line->appendChild($Quantity);
                             $Line->appendChild($UnitOfMeasure);
                             $Line->appendChild($UnitPrice);
                             $Line->appendChild($TaxPointDate);
                             $Line->appendChild($Description);
                             $Line->appendChild($CreditAmount);
                             $excepcao=$this->mIva->pega_exepcao($idProduto);
                             $Tax= $xml->createElement("Tax");
                             $TaxType= $xml->createElement("TaxType","IVA");
                             $TaxCountryRegion= $xml->createElement("TaxCountryRegion","AO");
                             $TaxCode= $xml->createElement("TaxCode", strlen($excepcao)>1 ? "ISE" : "NOR");
                             $TaxPercentage= $xml->createElement("TaxPercentage", strlen($excepcao)>1 ? 0 : 14.00);
                             $TaxExemptionReason= $xml->createElement("TaxExemptionReason",$this->ProdutosM->mpega_exepcao($idProduto));
                             $TaxExemptionCode= $xml->createElement("TaxExemptionCode",$excepcao);
                             $Tax->appendChild($TaxType);
                             $Tax->appendChild($TaxCountryRegion);
                             $Tax->appendChild($TaxCode);
                             $Tax->appendChild($TaxPercentage);
                             $Line->appendChild($Tax);
                             if(strlen($excepcao)>1){
                              $Line->appendChild($TaxExemptionReason);
                              $Line->appendChild($TaxExemptionCode);
                             }
                             //
                             $Invoice->appendChild($Line);
                             $ivafornecedor=$this->mIva->iva_fornecedor($idProduto);
                             $totalIvaFornecedor=$totalIvaFornecedor+$ivafornecedor;
                             $desconto = $this->calcuralDesconto($linha->desconto,$linha->qtd,$linha->custo);
                             
                             $totalDescontos=$totalDescontos+$desconto;
                             $total = $this->acharTotal($linha->desconto,$linha->qtd,$linha->custo);
                             $subtotal=$subtotal+ $total;
                             $ivaInt=$this->mIva->calculo_iva_int($total,$idProduto);
                             $totalIva=$totalIva+$ivaInt;
                    }
                    //
                     $totalLiquido= $this->FaturasM->somarSubTotalLiquido($idFatuta);
                     $totalGroso= $totalIva+$totalLiquido; 
                     $DocumentTotals= $xml->createElement("DocumentTotals");
                     $TaxPayable= $xml->createElement("TaxPayable",$totalIva);
                     $NetTotal= $xml->createElement("NetTotal",$totalLiquido);
                     $GrossTotal= $xml->createElement("GrossTotal",$totalGroso);
                     $DeductiblePercentage= $xml->createElement("DeductiblePercentage",14);
                     $DocumentTotals->appendChild($TaxPayable);
                     $DocumentTotals->appendChild($NetTotal);
                     $DocumentTotals->appendChild($GrossTotal);
                     $DocumentTotals->appendChild($DeductiblePercentage);
                    //
                    $Invoice->appendChild($DocumentTotals);
                    $SalesInvoices->appendChild($Invoice);
                //    $root->appendChild($SalesInvoices);
            }
          return  $xml->appendChild($SalesInvoices);
     }
      public function criar_tag_Source_documents($xml){// tag que contem infotmacao dos clientes e produtos
           $dataminima='';
           $datamaxima='';// 
           $root = $xml->createElement("SourceDocuments");
           
           $root->appendChild($this->pega_as_faturas_normas_e_anuladas($xml));
            
          return  $xml->appendChild($root);
     }
     public function criar_tag_master_file($xml){// tag que contem infotmacao dos clientes e produtos
            $dataminima='';
            $datamaxima='';
            $root = $xml->createElement("MasterFiles");
            $this->load->model('FaturasM');
            $clientes=$this->FaturasM->saftxml_clientes($dataminima,$datamaxima);// verifica se nº da  fatura ja existe
            foreach ($clientes as $linha) {
                    $Customer = $xml->createElement("Customer");
                    $CustomerID = $xml->createElement("CustomerID",$linha->idcliente);
                    $AccountID = $xml->createElement("AccountID","Desconhecido");
                    $CustomerTaxID = $xml->createElement("CustomerTaxID",strlen ($linha->nif) > 0 ? $linha->nif : "Consumidor final");
                    $CompanyName = $xml->createElement("CompanyName",$linha->nome);
                    // crira a tag endereço de cobrança
                    $BillingAddress = $xml->createElement("BillingAddress");
                    $AddressDetail = $xml->createElement("AddressDetail",$linha->localizacao);
                    $City = $xml->createElement("City","Huambo");
                    $Country = $xml->createElement("Country","AO");
                    // inserir filhos na tag endereço de cobrança
                     $BillingAddress->appendChild($AddressDetail);
                     $BillingAddress->appendChild($City);
                     $BillingAddress->appendChild($Country);
                    //crira a tag Enviar para o endereço
                    $ShipToAddress = $xml->createElement("ShipToAddress");
                     $AddressDetail1 = $xml->createElement("AddressDetail",$linha->localizacao);
                     $City1 = $xml->createElement("City","Huambo");
                     $Country1 = $xml->createElement("Country","AO");
                    // inserir filhos na tag Enviar para o endereço
                     $ShipToAddress->appendChild($AddressDetail1);
                     $ShipToAddress->appendChild($City1);
                     $ShipToAddress->appendChild($Country1);
                    //
                    $SelfBillingIndicator = $xml->createElement("SelfBillingIndicator",0);
                    $Telephone = $xml->createElement("Telephone",$linha->fone);
                    $Fax = $xml->createElement("Fax",$linha->fone);
                    $Email = $xml->createElement("Email",$linha->email);
                    $Website = $xml->createElement("Website");
                     //
                    $Customer->appendChild($CustomerID);
                    $Customer->appendChild($AccountID);
                    $Customer->appendChild($CustomerTaxID);
                    $Customer->appendChild($CompanyName);
                    $Customer->appendChild($BillingAddress);
                    $Customer->appendChild($ShipToAddress);
                    $Customer->appendChild($ShipToAddress);
                    $Customer->appendChild($Telephone);
                    $Customer->appendChild($Fax);
                    $Customer->appendChild($Email);
                    $Customer->appendChild($Website);
                    $Customer->appendChild($SelfBillingIndicator);
                    $root->appendChild($Customer);
            }
           $produtos=$this->FaturasM->saftxml_produtos($dataminima,$datamaxima);// verifica se nº da  fatura ja existe
           foreach ($produtos as $linha) {// saftxml_produtos
                    $Product = $xml->createElement("Product"); 
                    $ProductType = $xml->createElement("ProductType", $this->verifica_tipo($linha->tipo));
                    $ProductCode = $xml->createElement("ProductCode", $linha->idpr);
                    $ProductGroup = $xml->createElement("ProductGroup", $this->verifica_categoria($linha->idcategoria));
                    $ProductDescription = $xml->createElement("ProductDescription", $linha->produto);
                    $ProductNumberCode= $xml->createElement("ProductNumberCode", "Desconhecido");
                    
                    $Product->appendChild($ProductType);
                    $Product->appendChild($ProductCode);
                    $Product->appendChild($ProductGroup);
                    $Product->appendChild($ProductDescription);
                    $Product->appendChild($ProductNumberCode);
                    $root->appendChild($Product);
            }
           $TaxTable= $xml->createElement("TaxTable");
           $impostos=$this->FaturasM->saftxml_iva($dataminima,$datamaxima);// verifica se nº da  fatura ja existe
           foreach ($impostos as $linha) {// saftxml_produtos
                    $TaxTableEntry = $xml->createElement("TaxTableEntry"); 
                    $TaxType = $xml->createElement("TaxType", "IVA");
                    $TaxCode = $xml->createElement("TaxCode", "NOR");
                    $Description = $xml->createElement("Description", "IMPOSTO SOBRE VALOR ACRESCENTADO");
                    $TaxPercentage = $xml->createElement("TaxPercentage", 14.00);
                    
                  
                    
                    $TaxTableEntry->appendChild($TaxType);
                    $TaxTableEntry->appendChild($TaxCode);
                    $TaxTableEntry->appendChild($Description);
                    $TaxTableEntry->appendChild($TaxPercentage);
                    $TaxTable->appendChild($TaxTableEntry);
                    $root->appendChild($TaxTable);
            }
          return  $xml->appendChild($root);
     }
    public function criar_tag_header($xml){
     
            $header = $xml->createElement("Header");
            $CompanyAddress = $xml->createElement("CompanyAddress");
        
            $AuditFileVersion = $xml->createElement("AuditFileVersion", "1.01_01");
            $CompanyID = $xml->createElement("CompanyID", "5125004898");
            $TaxRegistrationNumber = $xml->createElement("TaxRegistrationNumber", "5125004898");
            $TaxAccountingBasis = $xml->createElement("TaxAccountingBasis", "F");
            $CompanyName = $xml->createElement("CompanyName", "ACATRONICS  ,LDA");
            $BusinessName = $xml->createElement("BusinessName", "ACATRONICS  ,LDA NIF: 2501017498");
           
            $BuildingNumber = $xml->createElement("AddressDetail" ,"ESTRADA PRINCIPAL DO SÃO PEDRO");

            $City = $xml->createElement("City" ,"Huambo");
            $Country = $xml->createElement("Country" ,"AO");
            // adicionar nos a tag CompanyAddress
            $CompanyAddress->appendChild($BuildingNumber);
            $CompanyAddress->appendChild($City);
            $CompanyAddress->appendChild($Country);
            $FiscalYear = $xml->createElement("FiscalYear" ,"2019");
            $StartDate = $xml->createElement("StartDate" ,"2019-08-07");
            $EndDate = $xml->createElement("EndDate" ,"2019-08-12");
            $CurrencyCode = $xml->createElement("CurrencyCode" ,"AOA");
            $DateCreated = $xml->createElement("DateCreated" , date('Y-m-d'));
            $TaxEntity = $xml->createElement("TaxEntity" ,"Global");
            $ProductCompanyTaxID = $xml->createElement("ProductCompanyTaxID" ,"100615626HO0305");
            $SoftwareValidationNumber = $xml->createElement("SoftwareValidationNumber" ,"1/AGT/2019");
            $ProductID = $xml->createElement("ProductID" ,"SGESOFT/FERNANDO VINEVALA GIDEAO");
            $ProductVersion= $xml->createElement("ProductVersion" ,"V1.0.1");
            $Telephone= $xml->createElement("Telephone" ,"931-166-750");
            $Fax= $xml->createElement("Fax");
            $Email= $xml->createElement("Email","8899442@gmail.com");
            $Website= $xml->createElement("Website");
            #adiciona os nós (informacaoes do contato) em contato
            $header->appendChild($AuditFileVersion);
            $header->appendChild($CompanyID);
            $header->appendChild($TaxRegistrationNumber);
            $header->appendChild($TaxAccountingBasis);
            $header->appendChild($CompanyName);
            $header->appendChild($BusinessName);
            $header->appendChild($CompanyAddress);
            $header->appendChild($FiscalYear);
            $header->appendChild($StartDate);
            $header->appendChild($EndDate);
            $header->appendChild($CurrencyCode);
            $header->appendChild($DateCreated);
            $header->appendChild($TaxEntity);
            $header->appendChild($ProductCompanyTaxID);
            $header->appendChild($SoftwareValidationNumber);
            $header->appendChild($ProductID);
            $header->appendChild($ProductVersion);
            $header->appendChild($Telephone);
            $header->appendChild($Fax);
            $header->appendChild($Email);
            $header->appendChild($Website);
            #adiciona o nó contato em (root) agenda
           return $xml->appendChild($header);      
 }
 public function gerar_saft_faturas(){// metódo Principal
            libxml_use_internal_errors(true);
            $dataminima= $this->input->get('dataminima');
            $datamaxima= $this->input->get('datamaxima'); 
            $this->load->model('mNotaCredito'); 
             $this->load->model('mIva');
            $this->load->model('ProdutosM');
            $xml = new DOMDocument("1.0", "UTF-8");// instanciar o  DOMDocument para a criação do xml
            #retirar os espacos em branco
            $xml->preserveWhiteSpace = false;
            #gerar o codigo
            $xml->formatOutput = true;
             #criando a tag (AuditFile) 
            $AuditFile = $xml->createElement("AuditFile"); 

            #adiciona o nó header em ($AuditFile) AuditFile

            $AuditFile->appendChild($this->criar_tag_header($xml));// adicionar a tag header  na tag AuditFile
            $AuditFile->appendChild($this->criar_tag_master_file($xml));
            $AuditFile->appendChild($this->criar_tag_Source_documents($xml));
            $xml->appendChild($AuditFile);
            
            # Para salvar o arquivo, descomente a linha
            $xml->save("SAFT-AO.xml");
                    #cabeçalho da página
            header("Content-Type: text/xml");
            # imprime o xml na tela
            print $xml->saveXML();      
 }
// public function gerar_saft_faturas1(){
//            libxml_use_internal_errors(true);
//            $dataminima= $this->input->get('dataminima');
//            $datamaxima= $this->input->get('datamaxima'); 
//            $xml = new DOMDocument("1.0", "Windows-1252");
//            #retirar os espacos em branco
//            $xml->preserveWhiteSpace = false;
//            #gerar o codigo
//            $xml->formatOutput = true;
//            #criando o nó principal (root)
//        
//            #nó filho (contato)
//            $contato = $xml->createElement("contato");
//            #setanto nomes e atributos dos elementos xml (nós)
//            $nome = $xml->createElement("nome", "Rafael Clares");
//            $telefone = $xml->createElement("telefone", "(11) 5500-0055");
//            $endereco = $xml->createElement("endereco", "Av. longa n 1");
//            #adiciona os nós (informacaoes do contato) em contato
//            $contato->appendChild($nome);
//            $contato->appendChild($telefone);
//            $contato->appendChild($endereco);
//            #adiciona o nó contato em (root) agenda
//            $root->appendChild($contato);
//            $xml->appendChild($root);
//            # Para salvar o arquivo, descomente a linha
//            $xml->save("saft.xml");
//            $xml->save('php://output');
//                    #cabeçalho da página
//            header("Content-Type: text/xml");
//            # imprime o xml na tela
//            print $xml->saveXML();      
// }
//   public function enviSaft(){
//    $this->load->library('email');
//  $arquivo = "SAFT-AO.xml";
//   //Variável $fp armazena a conexão com o arquivo e o tipo de ação.
//  // echo  load($arquivo);  
//$this->email->from("8899442@gmail.com", 'ACATRONICS  ,LDA');
//$this->email->subject("SAFT");
//$this->email->to("dinho-mesaque@hotmail.com"); 
//$this->email->message("Aqui vai a mensagem ao seu destinatário");
//$this->email->attach('SAFT-AO.xml');
//if ($this->email->send()) {
//            echo 'Your Email has successfully been sent.';
//        } else {
//            show_error($this->email->print_debugger());
//    }
////
// }
}

?>
