<% if Tickets %>
<style>
.CodebaseHQDashboardPanel { border-bottom: 1px solid silver}
.CodebaseHQDashboardPanel ul li {line-height: 22px;}
.CodebaseHQDashboardPanel ul li a {display: inline}
.CodebaseHQDashboardPanel .supportadmin-status {
    float: right;
}
</style>
    <% loop Tickets %>
        <ul>
          <li><a href ="$Link" target="codebasehq">$summary</a><span class="supportadmin-status status-{$StatusColor}">$StatusName</span></li>
        </ul>
    <% end_loop %>
<% end_if %>

