<?php

require_once 'config.php';

$sesion = Session::get_instance();

ini_set("display_errors", 1);
$tmpl_tecnicas = ApiBd::obtener_tecnicas();

/*
$tmpl_tecnicas = array(
    array(
        "nombre" => "Inyección",
        "links" => array(
            array(
                "href" => "tecnica.php?id=1",
                "nombre" => "Inyección SQL"
            ),
            array(
                "href" => "",
                "nombre" => "Inyección LDAP"
            ),
            array(
                "href" => "",
                "nombre" => "Inyección XML"
            ),
            array(
                "href" => "",
                "nombre" => "Inyección NoSQL"
            )
        )
    ),
    array(
        "nombre" => "Sitios cruzados",
        "links" => array(
            array(
                "href" => "tecnica.php?id=2",
                "nombre" => "XSS"
            ),
            array(
                "href" => "",
                "nombre" => "Falsificación de peticiones en sitios cruzados (CSRF)"
            )
        )
    ),
    array(
        "nombre" => "Control de acceso",
        "links" => array(
            array(
                "href" => "tecnica.php?id=2",
                "nombre" => "Pérdida de autenticación y gestión de sesiones"
            ),
            array(
                "href" => "",
                "nombre" => "Inexistente control de acceso a nivel de funcionalidades"
            ),
            array(
                "href" => "",
                "nombre" => "Exposición a datos sensibles"
            ),
            array(
                "href" => "",
                "nombre" => "Referencia directa insegura a objetos"
            )
        )
    ),
    array(
        "nombre" => "Configuración",
        "links" => array(
            array(
                "href" => "tecnica.php?id=2",
                "nombre" => "Configuración de seguridad incorrecta"
            ),
            array(
                "href" => "",
                "nombre" => "Uso de componentes con vulnerabilidades conocidas"
            )
        )
    ),
    array(
        "nombre" => "Inclusión",
        "links" => array(
            array(
                "href" => "tecnica.php?id=2",
                "nombre" => "LFI"
            ),
            array(
                "href" => "",
                "nombre" => "RFI"
            )
        )
    )
);*/
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <link href="css/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
        <link href="css/estilo.css" rel="stylesheet" />
        <script src="js/jquery.js"></script>
        <script src="css/bootstrap/js/bootstrap.min.js"></script>
        <title>LabSis - Seg</title>
    </head>
    <body>
        <main class="container">
            <h1>LabSis - Seg</h1>
            <h3>Técnicas</h3>
            <div class="row">
                <div class="col-sm-12">
                    <div class="row">
                        <?php foreach ($tmpl_tecnicas as $tmpl_tecnica): ?>
                            <div class="col-sm-3">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h3 class="panel-title"><?php echo $tmpl_tecnica["nombre"]; ?></h3>
                                    </div>
                                    <div class="panel-body">
                                        <ul>
                                            <?php foreach ($tmpl_tecnica["links"] as $link): ?>
                                                <li>
                                                    <a href="src/tecnica.php?id=<?php echo $link["href"]; ?>">
                                                        <?php echo $link["nombre"]; ?>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </main>
    </body>
</html>
