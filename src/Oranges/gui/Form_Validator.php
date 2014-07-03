<?php
namespace Oranges\gui;

class Form_Validator
{
    public function printHead()
    {
?>
<script type="text/javascript" src="js/separateFiles/dhtmlSuite-common.js"></script>
<script type="text/javascript">
<!--
<? // DHTML form validator ?>
DHTMLSuite.include('formValidator');
DHTMLSuite.include('form');
// -->
</script>
<?
    }

    public function printJs($formName, $enableSubmit = false)
    {
?>

<script type="text/javascript">
function enableSubmit()
{
<? if ($enableSubmit) { ?>
	document.getElementById('btnSubmit').disabled = false;	
<? } ?>
}

function disableSubmit()
{
<? if ($enableSubmit) { ?>
	document.getElementById('btnSubmit').disabled = true;
<? } ?>
}

var formValObj = new DHTMLSuite.formValidator({ formRef:'<?= $formName ?>',keyValidation:true,callbackOnFormValid:'enableSubmit',callbackOnFormInvalid:'disableSubmit',indicateWithBars:false });

var formObj = new DHTMLSuite.form({ formRef:'<?= $formName ?>',action:'includes/formSubmit.php',responseEl:'formResponse'});

</script>

<?
    }
}

?>
