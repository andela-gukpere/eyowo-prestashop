{if $eyw_payment == 1}
<p class="payment_module">
<a href="{$this_path}direct/redirect.php" title="{l s='Pay with your Debit/Credit' mod='eyowo'}">
<img src="https://www.eyowo.com/images/buttons/1.png" alt="{l s='Pay with your Debit/Credit' mod='eyowo'}" />
{l s='Pay with your Verve, Master, eTranszact or Interswitch Cards' mod='eyowo'}
</a>
<p style="font-size:15px;font-weight:bold;">Eyowo Transaction ID {$eyw_trans_id}</p>
{/if}