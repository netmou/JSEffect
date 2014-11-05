-- 本函数目的通过执行过程函数减少PHP链接数据库的次数
-- 如下查询所有pid为$pid，或为$pid下级的数据
-- example select * from group_info where find_in_set(`pid`,getTreeNodes({$pid}))>0;
DELIMITER // -- 在客户端中执行该语句，在phpmyadmin 中应在查询窗口中设置分界符
CREATE FUNCTION `getTreeNodes`(`rootId` INT)
RETURNS VARCHAR(255)
BEGIN
    DECLARE temp VARCHAR(255);
    DECLARE childlist VARCHAR(255);
    SET temp = '-1';
    SET childlist =cast(rootId as CHAR);
    WHILE childlist is not null DO
        SET temp = concat(temp,',',childlist);
        SELECT group_concat(id) INTO childlist FROM group_info where FIND_IN_SET(pid,childlist)>0;
    END WHILE;
    RETURN temp;
END
