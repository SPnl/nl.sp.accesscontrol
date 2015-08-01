{capture assign="block_links_afdelingen"}{strip}
    <li class="crm-afd-action">
        <a href="{$link_afdedit}" class="button" title="Wijzig contactgegevens">
            <span>{ts}Wijzig contactgegevens{/ts}</span>
        </a>
    </li>
{/strip}{/capture}


<script type="text/javascript">
    {literal}
    cj(function() {
        cj('li.crm-summary-block').after('{/literal}{$block_links_afdelingen}{literal}');
    });
    {/literal}
</script>