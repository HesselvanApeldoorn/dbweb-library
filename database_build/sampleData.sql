INSERT INTO User VALUES('sample@hotmail.com', 'user1', 'user1');
INSERT INTO User VALUES('sample2@hotmail.com', 'user2', 'user2');
INSERT INTO User VALUES('sample3@hotmail.com', 'user3', 'user3');

INSERT INTO PreferredCategory VALUES('sample@hotmail.com', 'fantasy');
INSERT INTO PreferredCategory VALUES('sample@hotmail.com', 'comedy');
INSERT INTO PreferredCategory VALUES('sample2@hotmail.com', 'fantasy');
INSERT INTO PreferredCategory VALUES('sample2@hotmail.com', 'action');
INSERT INTO PreferredCategory VALUES('sample3@hotmail.com', 'adventure');


INSERT INTO Notification VALUES(1, 'sample3@hotmail.com', 'hey gimme book', '20120301');
INSERT INTO Notification VALUES(2, 'sample2@hotmail.com', 'hey', '20110921');
INSERT INTO Notification VALUES(3, 'sample2@hotmail.com', 'bye', '20120809');
INSERT INTO Notification VALUES(4, 'sample@hotmail.com', 'you wrecked my book!!', '20121212');
INSERT INTO Notification VALUES(5, 'sample@hotmail.com', 'ffs', '20121212');
INSERT INTO Notification VALUES(6, 'sample@hotmail.com', 'ffs2', '20121213');


INSERT INTO Document VALUES(1, 'JK Rowling', 'first book of the serie', 'Philosophers stone', True, '0-7475-3269-9');
INSERT INTO Document VALUES(2, 'JK Rowling', 'second book of the serie', 'Chamber of secrets', True, '0-7475-3849-2');
INSERT INTO Document VALUES(3, 'JK Rowling', 'third book of the serie', 'Prisoner of Azkaban', True, null);
INSERT INTO Document VALUES(4, 'Stephen King', 'book about a tower', 'The Dark Tower', False, null);
INSERT INTO Document VALUES(5, 'Frank Brokken', 'lecture encryption', 'encryption.odp', True, null);
INSERT INTO Document VALUES(6, 'Frank Brokken', 'lecture hashing', 'hashing.odp', True, null);
INSERT INTO Document VALUES(7, 'Frank Brokken', 'lecture protocols', 'protocols.odp', False, null);
INSERT INTO Document VALUES(8, 'Cagri Coltekin', 'lecture sesson management', 'DBweb04-Sessions-Security.pdf', True, null);


INSERT INTO PaperDoc VALUES(1, 'new', 'sample@hotmail.com');
INSERT INTO PaperDoc VALUES(2, 'good', 'sample@hotmail.com');
INSERT INTO PaperDoc VALUES(3, 'poor', 'sample2@hotmail.com');
INSERT INTO PaperDoc VALUES(4, 'new', 'sample3@hotmail.com');


INSERT INTO ElectronicDoc VALUES(5,True, 'odp', 'f5');
INSERT INTO ElectronicDoc VALUES(6,False, 'odp', 'f6');
INSERT INTO ElectronicDoc VALUES(7,True, 'odp', 'f7');
INSERT INTO ElectronicDoc VALUES(8,True, 'pdf', 'f8');


INSERT INTO ElectronicDocCopies VALUES('sample@hotmail.com', 5);
INSERT INTO ElectronicDocCopies VALUES('sample@hotmail.com', 6);
INSERT INTO ElectronicDocCopies VALUES('sample2@hotmail.com', 6);
INSERT INTO ElectronicDocCopies VALUES('sample3@hotmail.com', 6);
INSERT INTO ElectronicDocCopies VALUES('sample@hotmail.com', 7);
INSERT INTO ElectronicDocCopies VALUES('sample2@hotmail.com', 7);
INSERT INTO ElectronicDocCopies VALUES('sample@hotmail.com', 8);
INSERT INTO ElectronicDocCopies VALUES('sample3@hotmail.com', 8);


INSERT INTO DocCategory VALUES(1, 'fantasy');
INSERT INTO DocCategory VALUES(1, 'adventure');
INSERT INTO DocCategory VALUES(2, 'fantasy');
INSERT INTO DocCategory VALUES(2, 'adventure');
INSERT INTO DocCategory VALUES(3, 'action');
INSERT INTO DocCategory VALUES(4, 'action');
INSERT INTO DocCategory VALUES(4, 'fantasy');
INSERT INTO DocCategory VALUES(5, 'action');
INSERT INTO DocCategory VALUES(5, 'education');
INSERT INTO DocCategory VALUES(6, 'education');
INSERT INTO DocCategory VALUES(7, 'education');
INSERT INTO DocCategory VALUES(8, 'fantasy');


INSERT INTO Loaning VALUES(1, 2, '20110921', '20100821', 'sample@hotmail.com', 'sample2@hotmail.com');
INSERT INTO Loaning VALUES(2, 3, '20110921', '20100821', 'sample2@hotmail.com', 'sample3@hotmail.com');