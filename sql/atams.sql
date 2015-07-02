-- create database
-- Academic Teaching Affair Management System

-- create database ATAMS;
-- use ATAMS;

-- ----------------------------------------
-- create table
-- ----------------------------------------
create table student(
	stuid bigint not null primary key,
    wxid varchar(50),
    pwd varchar(256) not null,
    name varchar(20) not null,
    sex char(1) default 'M' check (sex in ('M','F')),
    ethnicgroups varchar(20) not null default '汉族',
    address varchar(50),
    birthplace varchar(50),
    birthday date,
    comments varchar(100) default '无',
    politicalstatus varchar(20),
    idcard char(18)
);

create table notice(
	notid int not null primary key,
    title varchar(256) not null,
    content text(1024),
    time datetime not null
);

create table teacher(
	tchid int not null primary key,
    name varchar(10) not null
);

create table classroom(
	clsid int not null primary key,
    name varchar(20) not null,
    capacity int not null
);

create table course(
	corid int not null primary key,
    name varchar(50) not null,
    tchid int,
    clsid int,

    constraint fk_cor_clsid foreign key (clsid) references classroom(clsid),
    constraint fk_cor_tchid foreign key (tchid) references teacher(tchid)
);

create table schedule(
	schid int not null primary key,
    corid01 int, -- 45 min per
    corid02 int,
    corid03 int,
    corid04 int,
    corid05 int,
    corid06 int,
    corid07 int,
    corid08 int,
    corid09 int,
    corid10 int,
    corid11 int,
    corid12 int,

    constraint fk_sch_corid01 foreign key (corid01) references course(corid),
    constraint fk_sch_corid02 foreign key (corid02) references course(corid),
    constraint fk_sch_corid03 foreign key (corid03) references course(corid),
    constraint fk_sch_corid04 foreign key (corid04) references course(corid),
    constraint fk_sch_corid05 foreign key (corid05) references course(corid),
    constraint fk_sch_corid06 foreign key (corid06) references course(corid),
    constraint fk_sch_corid07 foreign key (corid07) references course(corid),
    constraint fk_sch_corid08 foreign key (corid08) references course(corid),
    constraint fk_sch_corid09 foreign key (corid09) references course(corid),
    constraint fk_sch_corid10 foreign key (corid10) references course(corid),
    constraint fk_sch_corid11 foreign key (corid11) references course(corid),
    constraint fk_sch_corid12 foreign key (corid12) references course(corid)
);

create table daytime(
	dayid int not null primary key,
    schid int not null,
    stuid bigint not null,
    daydate date,

    constraint fk_day_stuid foreign key (stuid) references student(stuid),
    constraint fk_day_schid foreign key (schid) references schedule(schid)
);

create table exam(
	exmid int not null primary key,
    name varchar(50) not null,
    clsid int not null,
    begtime datetime not null,
    duration int not null,
    corid int not null,

    constraint fk_exm_clsid foreign key (clsid) references classroom(clsid),
    constraint fk_exm_corid foreign key (corid) references course(corid)
);

create table grade(
	grdid int not null primary key,
    stuid bigint not null,
    corid int not null,
    gradenew decimal(4,1),
    gradeold decimal(4,1),

    constraint fk_grd_stuid foreign key (stuid) references student(stuid),
    constraint fk_grd_corid foreign key (corid) references course(corid)
);


-- ----------------------------------------
-- insert values
-- ----------------------------------------
insert into student(stuid,wxid,pwd,name,sex,address,birthplace,politicalstatus,idcard,birthday)
values(201230,'','NmI4NmIyNzNmZjM0ZmNlMTlkNmI4MDRlZmY1YTNmNTc0N2FkYTRlYWEyMmYxZDQ5YzAxZTUyZGRiNzg3NWI0Yg==','李杰','M','1#302','安徽','共青团员','345656199401296785','1994-01-02');
insert into student(stuid,wxid,pwd,name,sex,address,birthplace,comments,politicalstatus,idcard,birthday)
values('201231','','NmI4NmIyNzNmZjM0ZmNlMTlkNmI4MDRlZmY1YTNmNTc0N2FkYTRlYWEyMmYxZDQ5YzAxZTUyZGRiNzg3NWI0Yg==','张洁','F','3#606','江苏','是留级生','中国共产党党员','344626199302012846','1992-10-22');
insert into student(stuid,wxid,pwd,name,sex,address,birthplace,comments,politicalstatus,idcard,birthday)
values('201232',null,'NmI4NmIyNzNmZjM0ZmNlMTlkNmI4MDRlZmY1YTNmNTc0N2FkYTRlYWEyMmYxZDQ5YzAxZTUyZGRiNzg3NWI0Yg==','王斌','M','2#222','安徽','共青团员','是班长','345656199404274569','1993-05-02');
insert into student(stuid,wxid,pwd,name,sex,address,birthplace,comments,politicalstatus,idcard,birthday)
values('201234',null,'NmI4NmIyNzNmZjM0ZmNlMTlkNmI4MDRlZmY1YTNmNTc0N2FkYTRlYWEyMmYxZDQ5YzAxZTUyZGRiNzg3NWI0Yg==','MewX','M','2#0310','安徽','共青团员','学习委员','123123123123123123','1994-05-13');

insert into notice(notid,title,content,time)
values('123','五一放假通知','本次五一放假时间从1号到3号，4号正常上课。', '2015-05-13 7:00');
insert into notice(notid,title,content,time)
values('210','??周年校庆放假通知','2015年5月17日放假。', '2015-03-13 7:00');

insert into teacher(tchid,name)
values('1','张三');
insert into teacher(tchid,name)
values('2','李四');
insert into teacher(tchid,name)
values('3','朱六');
insert into teacher(tchid,name)
values('4','王刚');
insert into teacher(tchid,name)
values('5','李聪');
insert into teacher(tchid,name)
values('6','王明');

insert into classroom(clsid,name,capacity)
values('1','新教101','40');
insert into classroom(clsid,name,capacity)
values('2','新教201','40');
insert into classroom(clsid,name,capacity)
values('3','新教301','40');
insert into classroom(clsid,name,capacity)
values('4','新教302','40');
insert into classroom(clsid,name,capacity)
values('5','新副101','120');
insert into classroom(clsid,name,capacity)
values('6','新副201','120');
insert into classroom(clsid,name,capacity)
values('7','新副203','120');

insert into course(corid,name,tchid,clsid)
values('1','计算机导论','2','1');
insert into course(corid,name,tchid,clsid)
values('2','操作系统','1','3');
insert into course(corid,name,tchid,clsid)
values('3','计算机组成原理','5','4');
insert into course(corid,name,tchid,clsid)
values('4','近距离无线通信技术','6','2');
insert into course(corid,name,tchid,clsid)
values('5','数据结构','3','6');
insert into course(corid,name,tchid,clsid)
values('6','物联网导论','4','7');
insert into course(corid,name,tchid,clsid)
values('7','通信原理','4','5');

-- 1-21 can be templete, to copy to random course
insert into schedule(schid,corid01,corid02,corid05,corid06) values('1','1','1','3','3');
insert into schedule(schid,corid03,corid04,corid05,corid06,corid07,corid08) values('2','2','2','4','4','5','5');
insert into schedule(schid,corid11,corid12) values('3','1','1');
insert into schedule(schid,corid01,corid02,corid05,corid06) values('4','6','6','3','3');
insert into schedule(schid,corid03,corid04,corid11,corid12) values('5','2','2','4','4');
insert into schedule(schid,corid01,corid02) values('6','5','5');
insert into schedule(schid,corid11,corid12) values('7','6','6');
insert into schedule(schid,corid03,corid04,corid05,corid06) values('8','2','2','5','5');
insert into schedule(schid,corid03,corid04,corid07,corid08) values('9','1','1','3','3');
insert into schedule(schid,corid01,corid02) values('10','4','4');
insert into schedule(schid,corid03,corid04,corid11,corid12) values('11','6','6','3','3');
insert into schedule(schid,corid03,corid04) values('12','2','2');
insert into schedule(schid,corid01,corid02) values('13','5','5');
insert into schedule(schid,corid11,corid12) values('14','6','6');
insert into schedule(schid,corid05,corid06,corid07,corid08) values('15','1','1','4','4');
insert into schedule(schid,corid03,corid04,corid07,corid08) values('16','2','2','3','3');
insert into schedule(schid,corid01,corid02,corid03,corid04) values('17','5','5','1','1');
insert into schedule(schid,corid03,corid04,corid11,corid12) values('18','6','6','3','3');
insert into schedule(schid,corid03,corid04) values('19','4','4');
insert into schedule(schid,corid01,corid02) values('20','2','2');
insert into schedule(schid,corid11,corid12) values('21','6','6');

insert into daytime(dayid, schid, stuid, daydate) values( '1', '1', '201230', '2015-05-13');

insert into exam(exmid,name,clsid,begtime,duration,corid)
values('1','计算机导论期中考试','5','2016-05-13 7:00','120','1');
insert into exam(exmid,name,clsid,begtime,duration,corid)
values('2','数据结构期末考试','6','2015-05-13 7:00','120','5');
insert into exam(exmid,name,clsid,begtime,duration,corid)
values('3','物联网导论重修考试','7','2015-04-13 7:00','120','6');
insert into exam(exmid,name,clsid,begtime,duration,corid)
values('4','操作系统期中考试','5','2015-11-13 7:00','120','2');
insert into exam(exmid,name,clsid,begtime,duration,corid)
values('5','计算机组成原理补考','4','2015-05-12 7:00','120','3');

insert into grade(grdid,stuid,corid,gradenew,gradeold)
values('1','201230','1','51.5','0.0');
insert into grade(grdid,stuid,corid,gradenew,gradeold)
values('2','201230','3','70.0','0.0');
insert into grade(grdid,stuid,corid,gradenew,gradeold)
values('3','201230','6','80.5','51.5');
insert into grade(grdid,stuid,corid,gradenew,gradeold)
values('4','201231','2','81.5','0.0');
insert into grade(grdid,stuid,corid,gradenew,gradeold)
values('5','201231','4','70.5','32.5');
insert into grade(grdid,stuid,corid,gradenew,gradeold)
values('6','201231','6','90.5','51.5');
insert into grade(grdid,stuid,corid,gradenew,gradeold)
values('7','201232','1','51.5','0.0');
insert into grade(grdid,stuid,corid,gradenew,gradeold)
values('8','201232','3','70.0','0.0');
insert into grade(grdid,stuid,corid,gradenew,gradeold)
values('9','201232','6','80.5','51.5');
insert into grade(grdid,stuid,corid,gradenew,gradeold)
values('10','201234','2','81.5','0.0');
insert into grade(grdid,stuid,corid,gradenew,gradeold)
values('11','201234','4','70.5','32.5');
insert into grade(grdid,stuid,corid,gradenew,gradeold)
values('12','201234','6','90.5','51.5');
