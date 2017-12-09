<h3>Membership Periods</h3>

{crmAPI var='result' entity='Periods' action='get' contact_id=$cid
return="contribution_id.total_amount,start_date,end_date"}
<table class=" dataTable">
    <thead>
        <tr>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Contributions</th>
        </tr>
    </thead>
    <tbody>
        {foreach from=$result.values item=periods}
        <tr>
            <td>{$periods.start_date|date_format}</td>
            <td>{$periods.end_date|date_format}</td>
            <td>
                {$periods.contribution_id.total_amount}
            </td>
        </tr>
        {/foreach}
    </tbody>
</table>


