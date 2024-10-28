CREATE TABLE FileExtensions (
  id SMALLINT NOT NULL PRIMARY KEY AUTO_INCREMENT,
  ext VARCHAR(16) UNIQUE,
  mediaType ENUM('image', 'video', 'audio', 'other', 'unknown') NOT NULL DEFAULT 'unknown'
);
CREATE TABLE FileFormats (
  id SMALLINT NOT NULL PRIMARY KEY AUTO_INCREMENT,
  format VARCHAR(16) UNIQUE
);
CREATE TABLE FilePaths (
  id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
  path VARCHAR(1024) UNIQUE
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
  fileExtensionId SMALLINT,
  fileFormatId SMALLINT DEFAULT NULL,
  hasAudio BOOLEAN default 0,
  thumbnailFile VARCHAR(64) DEFAULT NULL,

  UNIQUE KEY unique_filePathId_fileName (filePathId, fileName),

  CONSTRAINT fk_MediaFiles_FileFormats
    FOREIGN KEY (fileFormatId)
    REFERENCES FileFormats(id),
  
  CONSTRAINT fk_MediaFiles_FileExtensions
    FOREIGN KEY (fileExtensionId)
    REFERENCES FileExtensions(id),
  
  CONSTRAINT fk_MediaFiles_FilePaths
    FOREIGN KEY (filePathId)
    REFERENCES FilePaths(id)
);