<div class="grid-100 tablet-grid-100 mobile-grid-100">
    <h2 class="no-top-padding">TwistProtect: Firewall</h2>
    <a href="?status=enable" class="button fat blue float-right"><i class="fa fa-key"></i> Enable Firewall</a>
    <p>Visitors that have been blocked from accessing the site, these visitors will see a 403 error message for a specified period of time. Reasons for being block are too many failed login attempts, too many password resets, too many 403,404 requests or being manually blocked.</p>

    <dl>
        <dt>Watched IPs</dt><dd></dd>
        <dt>Blocked IPs</dt>
            <dd>Temporary</dd>
            <dd>Permanent</dd>
        <dt>Whitelisted IPs</dt><dd></dd>
    </dl>

    <h3>Blocked IPs</h3>
    <table>
        <thead>
        <tr>
            <th>IP Address</th>
            <th>Length</th>
            <th>Reason</th>
            <th>Expires</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        {data:blocked_ips}
        </tbody>
    </table>
    <div class="clear"></div>

    <h3>Whitelisted IPs </h3>
    <table>
        <thead>
        <tr>
            <th>IP Address</th>
            <th>Added</th>
            <th>Reason</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        {data:whitelist_ips}
        </tbody>
    </table>
    <div class="clear"></div>

    <form class="inline foldable large" action="{uri:}" method="post">
        <h2>Add an IP</h2>

        <label for="ip_address">IP Address</label>
        <input type="text" id="ip_address" name="ip_address" value="" placeholder="xxx.xxx.xxx.xxx">
        <p><small>Enter the IP address that you wish to apply add to the whitelist/blocklist</small></p>

        <label for="list_action">API Allowed Request Methods</label>
        <select id="list_action" name="list_action">
            <option value="ban">Block this IP</option>
            <option value="whitelist">Whitelist this IP</option>
        </select>

        <button type="submit" class="fat"><i class="fa fa-save"></i> Submit</button>
    </form>
</div>