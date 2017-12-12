<h3>Membership Periods</h3>
<table class="dataTable">
    <thead>
        <tr>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Contributions</th>
        </tr>
    </thead>
    <tbody>
        {foreach from=$periods item=period}
        <tr>
            <td>{$period.start_date|date_format}</td>
            <td>{$period.end_date|date_format}</td>
            <td>
                {if $period.total}
                    <a href="{crmURL p='civicrm/contact/view/contribution' q="&cid=`$cid`&reset=1&force=1"}">
                        {$period.currency}
                    </a>
                {else}
                    Nill
                {/if}
            </td>
        </tr>
        {/foreach}


    </tbody>
</table>


{literal}
<script type="text/javascript">
    CRM.$(function($) {
        $('.dataTable').dataTable({
            "order": [[ 0, "desc" ]]
        } );
    });
</script>
{/literal}


