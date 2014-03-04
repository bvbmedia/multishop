<?php
// mod.php?&amp;id=0&amp;M=web_txmultishopM1&amp;SET[function]='+this.options[this.selectedIndex].value,this);">
$pages=array();
foreach ($this->MOD_MENU['function'] as $key => $label)
{
	switch ($key)
	{
		case '2':
			$pages[$key]['title']			=$label;
			$pages[$key]['url']				='mod.php?&amp;id=0&amp;M=web_txmultishopM1&amp;SET[function]='.$key;
			$pages[$key]['description']	='Page for maintaining the Multishop Plugin. Here you can easily backup and restore your Multishop database, resize catalog images and do much much more.';
		break;
		case '3':
			$pages[$key]['title']			=$label;
			$pages[$key]['url']				='mod.php?&amp;id=0&amp;M=web_txmultishopM1&amp;SET[function]='.$key;
			$pages[$key]['description']	='Are you new to TYPO3 Multishop? Here you can find details about how to configure Multishop on this TYPO3 Installation.';						
		break;		
	}
	if ($pages[$key]['title'])
	{
		$pages[$key]['description']	.=' <strong><a title="'.htmlspecialchars('Go to '.$label).'" href="'.$pages[$key]['url'].'">'.$this->Typo3Icon('actions-document-view','Go to web site').' Go to '.$label.'</a></strong>';	
	}
}
$items='';
foreach ($pages as $page)
{
	$items.='<fieldset><legend><a href="'.$page['url'].'">'.htmlspecialchars($page['title']).'</a></legend>'.$page['description'].'</fieldset>';
}

	$title='Welcome to Multishop';
	$content.='
	<div class="shadow_bottom">
		<fieldset>
			<strong>'.$title.'</strong><br>
			'.$this->mod_info['description'].'<BR>			
			'.$items.'
		</fieldset>
	</div>
	';
	$this->content.=$this->doc->section($title,$content,0,1);		
?>