drop table if exists ElectronicDoc;
drop table if exists ElectronicDocCopies;
drop table if exists Notification;
drop table if exists DocCategory;
drop table if exists PreferredCategory;
drop table if exists PaperDoc;
drop table if exists Loaning;
drop table if exists Document;
drop table if exists User;


create table Document(docID INT, author VARCHAR(255), description TEXT, document_name VARCHAR(255) not null, visible BOOLEAN not null, isbn VARCHAR(30), primary key(docID));

create table ElectronicDoc(docID INT, distributable BOOLEAN not null, extension VARCHAR(10) not null, content LONGBLOB not null, primary key(docID), foreign key(docID) references Document(docID));

create table User(email VARCHAR(255), user_name VARCHAR(255) not null, password VARCHAR(15) not null, primary key(email));

create table Notification(notificationID INT, email VARCHAR(255), primary key(notificationID), foreign key(email) references User(email));

create table ElectronicDocCopies(email VARCHAR(255), docID INT, primary key(email,docID), foreign key(docID) references Document(docID), foreign key(email) references User(email));

create table PaperDoc(docID INT, state ENUM('new','good','decent','poor') not null, email VARCHAR(255) not null, primary key(docID), foreign key(docID) references Document(docID), foreign key(email) references User(email));  

create table DocCategory(docID INT, category VARCHAR(255) not null, primary key(docID), foreign key(docID) references Document(docID));

create table PreferredCategory(email VARCHAR(255), category VARCHAR(255) not null, primary key(email), foreign key(email) references User(email));

create table Loaning(loaningID INT, docID INT not null, end_date DATETIME not null, start_date DATETIME not null, fromUser VARCHAR(255) not null, toUser VARCHAR(255) not null, primary key(loaningID), foreign key(docID) references Document(docID), foreign key(fromUser) references User(email), foreign key(toUser) references User(email));

