		<script src="//code.jquery.com/jquery-3.1.0.min.js" type="text/javascript"></script>
		<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js" type="text/javascript"></script>

		<?php
		if(isset($js) && is_array($js))
			echo "\t\t<script src=\"" . implode("\" type=\"text/javascript\"></script>\n\t\t<script src=\"", $js) . "\" type=\"text/javascript\"></script>\n\n";

		if(isset($scripts)){
			echo "\t\t" . implode("\n\t\t", array(
				'<script>',
				'<!--',
				trim($scripts),
				'//-->',
				'</script>',
			)) . "\n\n";
		}
		?>

		<script>
			setInterval(function(){
				$.post('<?php echo getURL('refresh-session.json'); ?>');
			}, 300000);
		</script>
	</body>
</html>
