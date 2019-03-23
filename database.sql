-- Uncomment these lines to clear the tables after creating them
--DROP TABLE Individual_Artist1;
--DROP TABLE Liked_Song;
--DROP TABLE Favorite_List;
--DROP TABLE Users;
--DROP TABLE Song;
--DROP TABLE Album;
--DROP TABLE Record_Label1;
--DROP TABLE Music_Creator;

--COMMIT;

CREATE TABLE Music_Creator(
	mc_id INTEGER PRIMARY KEY,
	name CHAR(20) NOT NULL,
	number_of_members INTEGER,
	number_of_releases INTEGER,
	years_active INTEGER,
	country_of_origin CHAR(20),
	primary CHAR(20) NOT NULL,
	secondary CHAR(20));
	
CREATE TABLE Record_Label1(
	label_id INTEGER PRIMARY KEY,
	name CHAR(20) NOT NULL,
	founder CHAR(20),
	primary CHAR(20) NOT NULL,
	secondary CHAR(20),
	country CHAR(20),
	years_active INTEGER); 
		
CREATE TABLE Album(
	album_id INTEGER PRIMARY KEY,
	title CHAR(20) NOT NULL,
	number_of_songs INTEGER,
	release CHAR(4),
	running_time FLOAT,
	rating FLOAT,
	primary CHAR(20) NOT NULL,
	secondary CHAR(20),
	mc_id INTEGER,
	label_id INTEGER,
	FOREIGN KEY (mc_id) REFERENCES Music_Creator(mc_id) ON DELETE CASCADE,
	FOREIGN KEY (label_id) REFERENCES Record_Label1(label_id) ON DELETE SET NULL);
		
CREATE TABLE Song(
	song_id INTEGER PRIMARY KEY,
	title CHAR(20) NOT NULL,
	rating FLOAT,
	length FLOAT,
	release CHAR(4),
	primary CHAR(20) NOT NULL,
	secondary CHAR(20),
	album_id INTEGER,
	mc_id INTEGER,
	label_id INTEGER,
	FOREIGN KEY (album_id) REFERENCES Album(album_id) ON DELETE CASCADE,
	FOREIGN KEY (mc_id) REFERENCES Music_Creator(mc_id) ON DELETE CASCADE,
	FOREIGN KEY (label_id) REFERENCES Record_Label1(label_id) ON DELETE SET NULL);

CREATE TABLE Users(
	email CHAR(40) PRIMARY KEY,
	username CHAR(20) NOT NULL UNIQUE,
	password CHAR(20) NOT NULL,
	type INTEGER);
	
CREATE TABLE Favorite_List(
	email CHAR(40),
	fl_id INTEGER,
	PRIMARY KEY(fl_id),
	FOREIGN KEY (email) REFERENCES Users(email) ON DELETE SET NULL);
		
CREATE TABLE Liked_Song(
	email CHAR(40),
	fl_id INTEGER,
	song_id INTEGER,
	PRIMARY KEY (email, fl_id, song_id),
	FOREIGN KEY (email) REFERENCES Users(email) ON DELETE CASCADE,
	FOREIGN KEY (fl_id) REFERENCES Favorite_List(fl_id) ON DELETE CASCADE,
	FOREIGN KEY (song_id) REFERENCES Song(song_id) ON DELETE CASCADE);

CREATE TABLE Individual_Artist1(
	a_id INTEGER PRIMARY KEY,
	Age INTEGER,
	role CHAR(20),
	Instrument CHAR(20));

