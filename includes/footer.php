		<footer class="footer mt-auto py-3 text-center">
			<div class="container text-secondary">
				<i class="fa fa-github"></i> <a href="https://github.com/seikan/NodeList" target="_blank" class="text-secondary">Node List</a>
			</div>
		</footer>

		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.3/js/bootstrap.bundle.min.js"></script>

		<?php
		if (isset($js) && is_array($js)) {
			echo "\t\t<script src=\"" . implode("\" type=\"text/javascript\"></script>\n\t\t<script src=\"", $js) . "\" type=\"text/javascript\"></script>\n\n";
		}

		if (isset($scripts)) {
			echo "\t\t" . implode("\n\t\t", [
				'<script>',
				'<!--',
				trim($scripts),
				'//-->',
				'</script>',
			]) . "\n\n";
		}
		?>

		<script>
			setInterval(function(){
				$.post('<?php echo getURL('refresh-session.json'); ?>');
			}, 300000);
		</script>
	</body>
</html>
