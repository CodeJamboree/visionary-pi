CREATE TABLE MediaTypes (
  id SMALLINT NOT NULL PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(16) UNIQUE
);
CREATE TABLE VideoFormats (
  id SMALLINT NOT NULL PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(16) UNIQUE
);
CREATE TABLE AudioFormats (
  id SMALLINT NOT NULL PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(16) UNIQUE
);
CREATE TABLE FileFormats (
  id SMALLINT NOT NULL PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(16) UNIQUE
);
CREATE TABLE FileExtensions (
  id SMALLINT NOT NULL PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(8) UNIQUE
);
CREATE TABLE AudioChannels (
  id SMALLINT NOT NULL PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(16) UNIQUE
);
CREATE TABLE AudioFrequencies (
  id SMALLINT NOT NULL PRIMARY KEY AUTO_INCREMENT,
  hz INT UNIQUE
);
CREATE TABLE FilePaths (
  id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(1024) UNIQUE
);

CREATE TABLE MediaFiles (
  id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
  filePathId INT,
  fileName VARCHAR(128),
  displayName VARCHAR(64),
  missing BOOLEAN default 0,
  fileSize INT,
  createdAt INT,
  width INT DEFAULT NULL,
  height INT DEFAULT NULL,
  duration INT DEFAULT NULL,
  mediaTypeId SMALLINT,
  fileExtensionId SMALLINT,
  fileFormatId SMALLINT DEFAULT NULL,
  audioChannelId SMALLINT DEFAULT NULL,
  audioFormatId SMALLINT DEFAULT NULL,
  audioFrequencyId SMALLINT DEFAULT NULL,
  videoFormatId SMALLINT DEFAULT NULL,

  UNIQUE KEY unique_filePathId_fileName (filePathId, fileName),

  CONSTRAINT fk_MediaFiles_MediaTypes
    FOREIGN KEY (mediaTypeId)
    REFERENCES MediaTypes(id),

  CONSTRAINT fk_MediaFiles_VideoFormats
    FOREIGN KEY (videoFormatId)
    REFERENCES VideoFormats(id),

  CONSTRAINT fk_MediaFiles_AudioChannels
    FOREIGN KEY (audioChannelId)
    REFERENCES AudioChannels(id),

  CONSTRAINT fk_MediaFiles_AudioFrequencies
    FOREIGN KEY (audioFrequencyId)
    REFERENCES AudioFrequencies(id),
  
  CONSTRAINT fk_MediaFiles_AudioFormats
    FOREIGN KEY (audioFormatId)
    REFERENCES AudioFormats(id),
  
  CONSTRAINT fk_MediaFiles_FileFormats
    FOREIGN KEY (fileFormatId)
    REFERENCES FileFormats(id),
  
  CONSTRAINT fk_MediaFiles_FileExtensions
    FOREIGN KEY (fileExtensionId)
    REFERENCES FileExtensions(id)
);

INSERT INTO MediaTypes (name) VALUES 
('Unknown'), ('Image'), ('Video'), ('Audio');