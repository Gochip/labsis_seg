USE labsis_seg;

-- T�cnicas padres
INSERT INTO tecnicas (nombre, id_padre) VALUES ("Injection", NULL);
INSERT INTO tecnicas (nombre, id_padre) VALUES ("Sitios cruzados", NULL);
INSERT INTO tecnicas (nombre, id_padre) VALUES ("Control de acceso", NULL);
INSERT INTO tecnicas (nombre, id_padre) VALUES ("Inclusi�n", NULL);

SET @injection = (SELECT id FROM tecnicas WHERE nombre="Injection");

-- Sub-t�cnicas 

INSERT INTO tecnicas (nombre, id_padre) VALUES ("SQL Injection", @injection);
INSERT INTO tecnicas (nombre, id_padre) VALUES ("LDAP Injection", @injection);
INSERT INTO tecnicas (nombre, id_padre) VALUES ("XML Injection", @injection);
INSERT INTO tecnicas (nombre, id_padre) VALUES ("NoSQL Injection", @injection);

