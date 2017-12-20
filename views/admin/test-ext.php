<?php //echo __FILE__;
//var_dump($this->blocks);exit;
//$all=$this->viewBlocks->all();var_dump($all);exit;
?>
<!--
? BEGIN:TEST-EXT-2B<br>

<h2>? <?= '@ news_2b_161124' ?></h2>

? +2B)This text is out of "parent"<br />
-->
<?php $this->startParent() ?>

+2B)#1  This text will NOT be lost ...<br /><br />

<?php $this->startBlock('header') ?>
   +2B)begin parentBlock...<br>
   <?php $this->parentBlock() ?>
   +2B)...end parentBlock.<br>

   +2B)Start block 'header'...<br />
   <h3>Redefined 2B header</h3>
   Some result in 2B = <?= $s ?> (var 's' @ 3B) <br />
   +2B)...stop block 'header'.<br /><br />

<?php $this->stopBlock('header') ?>

+2B)#2  This text will NOT be lost ...<br /><br />

<?php $this->startBlock('outer') ?>

    +2B)Start block 'outer'...<br />

    <?php $this->startBlock('inner') ?>
        +2B)Start block 'inner'...<br />
        N@2b = <?= $n ?> (var @ 3B)<br />
        +2B)...stop block 'inner'.<br /><br />
    <?php $this->stopBlock('inner') ?>

    +2B)...stop block 'outer'.<br /><br />

<?php $this->stopBlock('outer') ?>

+2B)#9  This text will NOT be lost ...<br /><br />

<?php $this->stopParent() ?>
<!--
? +2B)This text is out of "parent"<br />

? END:TEST-EXT-2B<br>
-->