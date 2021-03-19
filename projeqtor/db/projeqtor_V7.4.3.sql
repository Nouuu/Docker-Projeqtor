ALTER TABLE `${prefix}columnselector` 
CHANGE `widthPct` `widthPct` INT(3);

UPDATE `${prefix}columnselector` SET `widthPct`=-1 WHERE `objectClass`='GlobalView' and `attribute`='id';
