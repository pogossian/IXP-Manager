{include file="header.tpl"}

<ul class="breadcrumb">
    <li>
        <a href="{genUrl}">Home</a> <span class="divider">/</span>
    </li>
    <li>
        Statistics <span class="divider">/</span>
    </li>
    <li class="active">
        Graphs
        (
         {foreach from=$categories key=cname item=cvalue}{if $category eq $cvalue}{$cname}{/if}{/foreach}
        /
         {foreach from=$periods key=cname item=cvalue}{if $period eq $cvalue}{$cname}{/if}{/foreach}
        )
    </li>
</ul>

{include file="message.tpl"}

<p>
<form action="{genUrl controller="customer" action="statistics-overview"}" method="post">
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
    <td width="20"></td>
    <td valign="middle"><strong>Period:</strong></td>
    <td>
        <select name="period" onchange="this.form.submit();">
            {foreach from=$periods key=cname item=cvalue}
                <option value="{$cvalue}" {if $period eq $cvalue}selected{/if}>{$cname}</option>
            {/foreach}
        </select>
    </td>
</tr>
</table>
</form>
</p>


<div class="row-fluid">

{assign var='count' value=0}
{foreach from=$custs item=cust}

    <div class="span3">

        <h4>{$cust.name}</h4>

        <a href="{genUrl controller="dashboard" action="statistics" shortname=$cust.shortname monitorindex=aggregate category=$category}">
            <img
                src="{genMrtgImgUrl shortname=$cust.shortname category=$category period=$period monitorindex='aggregate'}"
                width="300"
            />
        </a>

    </div>

    {assign var='count' value=$count+1}

    {if $count%4 eq 0}
        </div><br /><div class="row-fluid">
    {/if}

{/foreach}

{if $count%4 neq 0}
    <div class="span3"></div>
    {assign var='count' value=$count+1}
    {if $count%4 neq 0}
        <div class="span3"></div>
        {assign var='count' value=$count+1}
        {if $count%4 neq 0}
            <div class="span3"></div>
            {assign var='count' value=$count+1}
        {/if}
    {/if}
{/if}

</div>


{include file="footer.tpl"}