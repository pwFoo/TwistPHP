<div id="twist-debug">
	<a href="https://twistphp.com/docs" target="_blank" id="twist-debug-icon" title="TwistPHP documentation"><img src="{resource:core-uri}twist/logos/logo-64.png"></a>
	<ul id="twist-debug-blocks">
		<li><a href="#twist-debug-messages"><span id="twist-debug-errors"><i class="fa fa-exclamation-circle"></i>{data:warning_count}</span><span id="twist-debug-warnings"><i class="fa fa-exclamation-triangle"></i>{data:notice_count}</span><span id="twist-debug-dumps"><i class="fa fa-comment"></i>{data:other_count}</span></a></li><!--
		--><li><a href="#twist-debug-timeline"><i class="fa fa-fw fa-lg fa-clock-o"></i>{data:execution_time}s</a></li><!--
		--><li><a href="#twist-debug-memory"><i class="fa fa-fw fa-lg fa-line-chart"></i>{bytestosize[data:memory/peak]}</a></li><!--
		--><li><a href="#twist-debug-database"><i class="fa fa-fw fa-lg fa-database"></i>{data:database_query_count}</a></li><!--
		--><li><a href="#twist-debug-route"><strong>GET</strong> {route:uri}</a></li>
	</ul>
</div>
<div id="twist-debug-details">
	<a href="#close-twist-debug-details"></a>
	<div id="twist-debug-messages">
		<h6>Messages</h6>
		<div class="twist-debug-column-wrapper">
			<div class="twist-debug-column-100">
				{data:errors}
			</div>
		</div>
	</div>
	<div id="twist-debug-timeline">
		<h6>Timeline</h6>
		<div class="twist-debug-column-wrapper">
			<div class="twist-debug-column-100">
				<div id="twist-debug-timeline-chart">
					<ul>
						{data:timeline}
					</ul>
				</div>
				<p>Execution Time: {data:execution_time}</p>
			</div>
			<div class="twist-debug-column-50"></div>
			<div class="twist-debug-column-50"></div>
		</div>
	</div>
	<div id="twist-debug-memory">
		<h6>Memory</h6>
		<div class="twist-debug-column-wrapper">
			<div class="twist-debug-column-100">
				<div id="twist-debug-memory-chart">
					<ul>
						{data:memory_chart}
					</ul>
				</div>
			</div>
			<div class="twist-debug-column-50">
				<p>
					Memory Limit: {data:memory/limit}
					Peak Memory: {bytestosize[data:memory/peak]}
					End Memory: {bytestosize[data:memory/end]}
				</p>
			</div>
			<div class="twist-debug-column-50"></div>
		</div>
	</div>
	<div id="twist-debug-database">
		<h6>Database Queries</h6>
		<div class="twist-debug-column-wrapper">
			<div class="twist-debug-column-100">
				{data:database_queries}
			</div>
		</div>
	</div>
	<div id="twist-debug-route">
		<h6>Routes</h6>
		<div class="twist-debug-column-wrapper">
			<div class="twist-debug-column-50">
				<table>
					<thead>
					<tr>
						<th>URI</th>
						<td>Method</td>
						<td>Type</td>
						<td>Call</td>
						<td>Restriction</td>
					</tr>
					</thead>
					<tbody>
					{data:routes}
					</tbody>
				</table>
				<ul>
					<li>Current Route</li>
					<li>{data:route_current}</li>
				</ul>
			</div>
			<div class="twist-debug-column-50">
				<ul>
					<li>$_GET</li>
					<li>{data:get}</li>
					<li>$_POST</li>
					<li>{data:post}</li>
					<li>$_COOKIE</li>
					<li>{data:cookie}</li>
					<li>Request Headers</li>
					<li>{data:request_headers}</li>
					<li>$_SERVER</li>
					<li>{data:server}</li>
				</ul>
			</div>
		</div>
	</div>
</div>
{resource:twist/debug}