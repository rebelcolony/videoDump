	<div class="main clearfix">

		<!-- COLUMN 1 -->
		<div class="threeColA1">
			<div class="contentblock">
				<h1>{title}</h1>
				<p>{entry}</p>
			</div>
			
			<div class="plug video clearfix">
				<div class="tagsbox2">
					<strong>Tags:</strong> {tags}
				</div>	
			</div>

			<div class="pagenav clearfix">
				{IF_PREV}<div class="left"><a href="{PREV}">&laquo; previous</a></div>{END_IF}
				{IF_NEXT}<div class="right"><a href="{NEXT}">next &raquo;</a></div>{END_IF}
			</div>
		</div>