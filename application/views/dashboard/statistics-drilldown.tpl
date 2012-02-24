{include file="header.tpl" pageTitle="IXP Manager :: Member Dashboard"}

{if $user.privs eq 3}
    <ul class="breadcrumb">
        <li>
            <a href="{genUrl}">Home</a> <span class="divider">/</span>
        </li>
        <li>
            <a href="{genUrl controller='customer' action='list'}">Customers</a> <span class="divider">/</span>
        </li>
        <li>
             {$customer.name} <span class="divider">/</span>
        </li>
        <li class="active">
            Statistics
            ({foreach from=$categories key=cname item=cvalue}{if $category eq $cvalue}{$cname}{/if}{/foreach})
        </li>
    </ul>
{else}
    <div class="page-content">
        <div class="page-header">
            <h1>IXP Interface Statistics :: {$customer->name} ({foreach from=$categories key=cname item=cvalue}{if $category eq $cvalue}{$cname}{/if}{/foreach})</h1>
        </div>
{/if}


{include file="message.tpl"}

<div id='ajaxMessage'></div>

{if $switchname eq ''}
    <h2>Aggregate Statistics for All Ports</h2>
{else}
    <h2>Switch: {$switchname} &nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp; Port: {$portname}</h2>
{/if}

<p>
<form action="{genUrl controller="dashboard" action="statistics-drilldown" shortname=$shortname monitorindex=$monitorindex}" method="post">
<table>
<tr>
    <td width="20"></td>
    <td valign="middle"><strong>Graph Type:</strong></td>
    <td>
        <select name="category" onchange="this.form.submit();">
            {foreach from=$categories key=cname item=cvalue}
                <option value="{$cvalue}" {if $category eq $cvalue}selected{/if}>{$cname}</option>
            {/foreach}
        </select>
    </td>
</tr>
</table>
</form>
</p>


<div class="row-fluid">

{assign var='count' value=0}

    {foreach from=$periods key=pname item=pvalue}

    <div class="span6">

        <div class="well">

            <h3>{$pname} Graph</h3>

            <p>
                {genMrtgGraphBox
                        shortname=$customer->shortname
                        category=$category
                        monitorindex=$monitorindex
                        period=$pvalue
                        values=$stats.$pvalue
                }
            </p>

        </div>
    </div>

    {assign var='count' value=$count+1}

    {if $count%2 eq 0}
        </div><br /><div class="row-fluid">
    {/if}


    {/foreach}

{if $count%2 neq 0}
    <div class="span3"></div>
{/if}

</div>


{include file="footer.tpl"}

