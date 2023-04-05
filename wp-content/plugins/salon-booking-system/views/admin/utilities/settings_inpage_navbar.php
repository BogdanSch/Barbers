<?php
function sum(...$items) {
	?>
	<div class="sln-inpage_navbar_wrapper <?php if (!$items) {echo " sln-inpage_navbar_wrapper--fk";}?>">
		<?php if ($items) {
		?>
		<a href="#sln-nav-tab-wrapper" class="sln-inpage_navbar__currenttab <?php if (isset($_GET['tab'])) {echo 'sln-inpage_navbar__icon--' . esc_attr($_GET['tab']);}?>">
			<span class="sr-only">go to main tabs menu</span>
		</a>
	<a href="#nogo" class="sln-inpage_navbar__scroller sln-inpage_navbar__scroller--left"><span class="sr-only">scroll right</span></a>
    <nav id="sln-inpage_navbar" class="sln-inpage_navbar_inner">
    	<ul class="nav nav-pills sln-inpage_navbar">

	<?php
$index = 0;
		foreach ($items as $i) {
			?>
				<li class="nav-item sln-inpage_navbaritem <?php if ($index == 0) {echo 'active';}?>">
		      		<a class="nav-link nav-link1 sln-inpage_navbarlink" href="<?php echo $i[0]; ?>">
		      			<span><?php echo $i[1]; ?></span>
		      		</a>
		    	</li>
			<?php
$index++;}?>
		</ul>
	</nav>
	<a href="#nogo" class="sln-inpage_navbar__scroller sln-inpage_navbar__scroller--right"><span class="sr-only">scroll right</span></a>
<?php }?>
</div>
<?php
}?>