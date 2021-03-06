<?php
require_once '../config.php';

$sesion = Session::get_instance();

$metodo = filter_input(INPUT_SERVER, "REQUEST_METHOD");
if (strcasecmp($metodo, "POST") === 0) {

    //header("Location: tecnica.php?id=$id_contenedor");
} else if (strcasecmp($metodo, "GET") === 0) {
    $id_articulo = filter_input(INPUT_GET, "id_articulo");
    $id_contenedor = filter_input(INPUT_GET, "id_contenedor");
    $tipo = filter_input(INPUT_GET, "tipo_contenedor");
    if(isset($id_articulo) && is_numeric($id_articulo)){
        $historial_articulos = ApiBd::obtener_historial_articulos($id_articulo);
        array_unshift($historial_articulos, array("fecha_hora" => "Actual", "id" => "-1"));
        $articulo_actual = ApiBd::obtener_version_articulo("-1", $id_articulo);
        $version_actual = $articulo_actual["version"];
        $titulo_actual = $articulo_actual["titulo"];


    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Historial de artículo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="<?php echo $WEB_PATH ?>css/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
    <link href="<?php echo $WEB_PATH ?>css/general.css" rel="stylesheet" />
    <link href='<?php echo $WEB_PATH ?>css/footer-distributed.css' rel="stylesheet"/>
    <style>
        .historico{
            position: float;
            float: left;
            margin-right: 40px;
            border-right: 1px solid black;
        }
        .historico li{
            cursor: pointer;
            margin: 5px;
        }
        .editor{

            float: left;
            width: 70%;
        }
        .seleccionado{
            font-weight: bold;
        }

        @media (max-width: 600px) {
            .editor{

                width: unset;
                text-align: center;
            }
        }

    </style>
    <script type="text/javascript" src="<?php echo $WEB_PATH ?>/js/jquery.js"></script>
    <script type="text/javascript" src="<?php echo $WEB_PATH ?>/js/ckeditor/ckeditor.js"></script>
    <script type="text/javascript" src="<?php echo $WEB_PATH ?>/css/bootstrap/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function(){
            CKEDITOR.replace("txtEdicion");
            $("#spanTituloEdicion").text("Actual");
            $("#tituloAutorEdicion").css("display", "none")

            $(".historico li").click(function(){
                var versionSeleccionada = $(this).text();
                var idVersion = $(this).data("id-version");
                let idAutor = $(this).data("id-autor");
                let nombreAutor = $(this).data("nombre-autor");


                if (parseFloat(idAutor) > 0){
                    $("#spanAutorEdicion").html(`
                             <a href='<?php echo $WEB_PATH ?><?php echo $CTRL_REL_PATH ?>ver_autor.php?id_autor=` + idAutor + `'>` + nombreAutor + `</a>
                        `)
                    $("#tituloAutorEdicion").css("display", "block")
                } else {
                    $("#spanAutorEdicion").html("");
                    $("#tituloAutorEdicion").css("display", "none")
                }

                var idArticulo = $("#hidIdArticulo").val();
                $.ajax({
                    "type": "post",
                    "url": "consultar_version_articulo.php",
                    "data": {
                        "id_version": idVersion,
                        "id_articulo": idArticulo
                    }
                }).done(function(r){
                    r = JSON.parse(r);
                    if(r.status === "ok"){
                        $("#txtTituloModalEditar").val(r.titulo);
                        CKEDITOR.instances['txtEdicion'].setData(r.version);
                    }
                });

                $("#spanTituloEdicion").text(versionSeleccionada);
                $(".seleccionado").removeClass("seleccionado");
                $(this).addClass("seleccionado");
            });
        });
    </script>
</head>
<body>
<?php require_once('../header.php') ?>
<main class="container">
    <?php $LINK_VOLVER = "{$WEB_PATH}{$CTRL_REL_PATH}contenedor.php?id=$id_contenedor&tipo=$tipo"?>
    <?php require_once $SERVER_PATH . $TEMPLATES_REL_PATH . 'maquetado/menu.tmpl.php' ?>
    <div class="row">
        <div class="col-sm-12">
            <h2>Historial de artículos</h2>
            <hr/>
        </div>
    </div>
    <input type="hidden" id="hidIdArticulo" value="<?php echo $id_articulo ?>" />
    <ul class="historico">
        <?php foreach($historial_articulos as $cambio): ?>
            <?php if($cambio['fecha_hora'] === "Actual") :?>
                <li class="seleccionado" data-id-version="<?php echo $cambio["id"] ?>">
                    <?php echo $cambio['fecha_hora'] ?>
                </li>
            <?php else: ?>
                <li data-id-version="<?php echo $cambio["id"] ?>"
                    data-id-autor="<?php echo (isset($cambio["id_autor"])) ? $cambio["id_autor"] : "-1" ?>"
                    data-nombre-autor="<?php echo (isset($cambio["nombre_autor"])) ? $cambio["nombre_autor"] : "" ?>">
                    <?php echo $cambio['fecha_hora'] ?>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>
    <div class="editor col-xs-12" >
        <form role="form" action="<?php echo $WEB_PATH . $CTRL_REL_PATH ?>editar_articulo.php" method="POST">
            <input type="hidden" value="<?php echo $id_contenedor ?>" name="id_contenedor" id="id_contenedor" />
            <input type="hidden" name="tipo" value="<?php echo $tipo ?>" />
            <input type="hidden" value="<?php echo $id_articulo ?>" name="hidIdArticuloModalEditar" id="hidIdArticuloModalEditar" />

            <h4>Edición de la versión de <span id="spanTituloEdicion"></span></h4>
            <h5 id="tituloAutorEdicion">por <span id="spanAutorEdicion"></span></h5>
            <div class="form-group">
                <label for="comment">Título:</label>
                <input type="text" class="form-control" name="txtTituloModalEditar" id="txtTituloModalEditar" value="<?php echo $titulo_actual ?>">
            </div>
            <div class="form-group">
                <textarea class="form-control" rows="20" id="txtEdicion" name="txtContenidoModalEditar" ><?php echo $version_actual ?></textarea>
            </div>
            <?php if ($sesion->is_active()): ?>
                <button type="submit" class="btn btn-primary pull-right">Guardar nueva versión</button>
            <?php endif; ?>
        </form>
    </div>
</main>
<?php require_once '../footer.php' ?>
</body>
</html>

