	<div class="main clearfix">

		<!-- COLUMN 1 -->
		<div class="threeColA1">
{plug}
			<div class="plug video clearfix">
				<h1>Comments</h1>
				<form action="{action}" method="post">
					<table class="form" cellpadding="2" cellspacing="2" border="0">
						<tr>
							<td>Comment Title:</td>
							<td>{title}</td>
						</tr>
						<tr>
							<td>Your Comment:</td>
							<td>{description}</td>
						</tr>
<?php if($this->rknclass->user['group']['captcha_enabled'] == '1'): ?>
						<tr>
							<td>Validation:</td>
							<td><img alt="Validation Code" src="{captcha[image]}" /></td>
						</tr>
						<tr>
							<td></td>
							<td>{captcha[input]}</td>
						</tr>
<?php endif; ?>
						<tr>
							<td></td>
							<td><input type="submit" value="Add Comment" /></td>
						</tr>
					</table>
				</form>
{comments}
			</div>

		</div>