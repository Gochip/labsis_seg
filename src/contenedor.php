<?php
require_once '../config.php';

$sesion = Session::get_instance();

$id_contenedor = filter_input(INPUT_GET, "id");
$tipo_contenedor = filter_input(INPUT_GET, "tipo");

$tmpl_contenedor = array();
try{
    $tmpl_contenedor = null;
    if (isset($tipo_contenedor) && isset($id_contenedor)) {
        if ($tipo_contenedor === "tecnica") {
            $tipo_contenedor = "tecnica";
            $tmpl_contenedor = ApiBd::obtener_tecnica($id_contenedor);
        } else if ($tipo_contenedor === "vulnerabilidad") {
            $tmpl_contenedor = ApiBd::obtener_vulnerabilidad($id_contenedor);
            $tipo_contenedor = "vulnerabilidad";
        } else if ($tipo_contenedor === "algoritmo") {
            $tmpl_contenedor = ApiBd::obtener_algoritmo($id_contenedor);
            $tipo_contenedor = "algoritmo";
        }
    }
    if (!isset($tmpl_contenedor)) {
        $sesion->add_error_message("Página no encontrada.");
    }
} catch(Exception $ex) {
    $sesion->add_error_message($ex->getMessage());
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <link rel="icon" href="../css/favicon.png">
    <title>LabSis - Seg</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="<?php echo $WEB_PATH ?>css/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
    <link href="<?php echo $WEB_PATH ?>css/general.css" rel="stylesheet" />
    <link href="<?php echo $WEB_PATH ?>css/contenedor.css" rel="stylesheet" />
    <link href='<?php echo $WEB_PATH ?>css/footer-distributed.css' rel="stylesheet"/>
    <script type="text/javascript" src="<?php echo $WEB_PATH ?>js/jquery.js"></script>

    <script type="text/javascript" src="<?php echo $WEB_PATH ?>js/js-sequence-diagram/webfont.js"></script>
    <script type="text/javascript" src="<?php echo $WEB_PATH ?>js/js-sequence-diagram/snap.svg-min.js"></script>
    <script type="text/javascript" src="<?php echo $WEB_PATH ?>js/js-sequence-diagram/underscore-min.js"></script>
    <script type="text/javascript" src="<?php echo $WEB_PATH ?>js/js-sequence-diagram/sequence-diagram-min.js"></script>


    <script type="text/javascript" src="<?php echo $WEB_PATH ?>js/ckeditor/ckeditor.js"></script>
    <script type="text/javascript" src="<?php echo $WEB_PATH ?>css/bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            var txtContenido = document.getElementById("txtContenido");
            if (txtContenido !== null) {
                CKEDITOR.replace("txtContenido");
            }
            var txtContenidoModalEditar = document.getElementById("txtContenidoModalEditar");
            if (txtContenidoModalEditar !== null) {
                CKEDITOR.replace("txtContenidoModalEditar");
            }

            CKEDITOR.config.toolbarCanCollapse = true;


            dibujarDiagramasSecuencia()

            // Borrado de artículo
            var articuloABorrarJQuery = null;
            function mostrarModalConfirmacionBorrar(idArticulo){
                $("#hidIdArticulo").val(idArticulo);
                var idContenedor = $("#hidIdContenedor").val();
                $("#hidIdContenedorModalBorrado").val(idContenedor);
                $("#modalConfirmarBorrado").modal("show");
            }

            // Hack para que se pueda usar CKEditor desde un modal de Bootstrap
            // https://stackoverflow.com/a/46442460/5258711
            $('#modalEditar').on('shown.bs.modal', function() {
                $(document).off('focusin.modal');
            });

            $(document).on("hide.bs.modal", "#modalConfirmarBorrado", function () {
                console.log(articuloABorrarJQuery);
                if(articuloABorrarJQuery != null){
                    articuloABorrarJQuery.removeClass("articuloSeleccionado");
                }
            });

            $(".borrar").click(function(){
                articuloABorrarJQuery = $(this).parents("section");
                articuloABorrarJQuery.addClass("articuloSeleccionado");
                var id = $(this).parents("section").data("id");
                mostrarModalConfirmacionBorrar(id);
            });

            // Edición de artículo
            function mostrarModalEditar(idArticulo, titulo, contenido){
                /*$.ajax({
                    url: "<?php //echo $WEB_PATH ?><?php //echo $CTRL_REL_PATH; ?>consultar_versiones_articulo.php",
                        type: "post",
                        data: {
                            idArticulo: idArticulo
                        }
                    }).done(function(r){
                        console.log(r);
                    });*/



                let callback = function(){

                    $(CKEDITOR.instances.txtContenidoModalEditar.document.$.documentElement).children("body").children(".divDiagramaSecuencia").each(function(i, e){
                        let htmlDiagrama = $(e).html()
                        htmlDiagrama =  htmlDiagrama.split("<br>").join("\n");
                        $(e).attr("content", htmlDiagrama)

                        $(e).html(`
                            <diagrama-secuencia>`  + htmlDiagrama + `</diagrama-secuencia>
                        `)
                    })

                    $(CKEDITOR.instances.txtContenidoModalEditar.document.$.documentElement.getElementsByTagName("diagrama-secuencia")).sequenceDiagram({theme: 'simple'});
                }
                CKEDITOR.instances['txtContenidoModalEditar'].setData(contenido,callback);

                //$(CKEDITOR.instances.txtContenidoModalEditar.document.$.documentElement).children("body").children(".svgDiagramaSecuencia").remove()
                // $(CKEDITOR.instances.txtContenidoModalEditar.document.$.documentElement).children("body").children(".htmlDiagramaSecuencia").css("display", "block")

                $("#txtTituloModalEditar").val(titulo);
                $("#hidIdArticuloModalEditar").val(idArticulo);
                $("#modalEditar").modal("show");


            }

            $(document).on("click", ".editar", function(){
                var titulo = $(this).parents("section").find(".titulo").text().trim();

                // $(this).parents(".contenido").children("diagrama-secuencia").children(".svgDiagramaSecuencia").remove()

                var contenido = $(this).parents(".contenido").clone()

                let htmlDiagrama = $(contenido).children(".divDiagramaSecuencia").children(".htmlDiagramaSecuencia").html();
                if (htmlDiagrama != null) {
                    htmlDiagrama =  htmlDiagrama.split("\n").join("<br>");
                    $(contenido).children(".divDiagramaSecuencia").replaceWith(`
                        <div class="divDiagramaSecuencia">` + htmlDiagrama + ` </div>
                    `)
                }
                

                contenido = contenido.html().trim();


                var idArticulo = $(this).parents("section").data("id");
                $("#btnVerHistorial").data("id-articulo", idArticulo);
                mostrarModalEditar(idArticulo, titulo, contenido);
            });

            // Ver historial
            function verHistorial(idArticulo, idContenedor, tipoContenedor){
                location.href = "<?php echo $WEB_PATH ?><?php echo $CTRL_REL_PATH ?>ver_historial_articulo.php?id_articulo=" + idArticulo + "&id_contenedor=" + idContenedor + "&tipo_contenedor=" + tipoContenedor;
            }

            $("#btnVerHistorial").click(function(){
                var idArticulo = $(this).data("id-articulo");
                var idContenedor = $("#hidIdContenedor").val();
                var tipoContenedor = $("#hidTipoContenedor").val();
                verHistorial(idArticulo, idContenedor, tipoContenedor);
            });

            // Ver artículos desactivados
            function mostrarArticulosDesactivados(idContenedor){
                $.ajax({
                    url: "ajax/consultar_articulos_desactivados.php",
                    type: "post",
                    data: {
                        id_tecnica: idContenedor
                    }
                }).done(function(r){
                    var articulosDesactivados = JSON.parse(r);
                    if(articulosDesactivados.status === "ok") {
                        var articulos = articulosDesactivados.articulos;
                        for(indiceArticulo in articulos){
                            var articulo = articulos[indiceArticulo];

                            // Creo el HTML
                            var seccionHtml = '<section class="articulo-desactivado" data-id="' + articulo.id +'">';
                            var tituloHtml = '<h3 class="titulo">';
                            var contenidoHtml = '<div class="contenido">';

                            tituloHtml += articulo.contenido.titulo;
                            contenidoHtml += articulo.contenido.version;

                            tituloHtml += "</h3>";
                            contenidoHtml += '<i class="editar glyphicon glyphicon-edit btn-animado" title="Editar"></i></div>';
                            seccionHtml += tituloHtml;
                            seccionHtml += contenidoHtml;

                            seccionHtml += "</section>";
                            $("#divSecciones").append(seccionHtml);
                        }
                    }
                    console.log(articulosDesactivados);
                }).fail(function(){
                    alert("Fail AJAX");
                });
            }

            function ocultarArticulosDesactivados(idContenedor){
                $(".articulo-desactivado").remove();
            }

            $("#chkMostrarArticulosDesactivados").change(function(){
                var idContenedor = $("#hidIdContenedor").val();
                if($(this).prop("checked")){
                    mostrarArticulosDesactivados(idContenedor);
                } else {
                    ocultarArticulosDesactivados(idContenedor);
                }
            });


            function dibujarDiagramasSecuencia(){
                $(".divDiagramaSecuencia").each(function(i,element){
                    let htmlDiagrama = $(element).attr("content")
                    $(element).html(
                        `<div class="htmlDiagramaSecuencia" style="display: none">` + htmlDiagrama + `</div>`
                        +
                        `<div class="svgDiagramaSecuencia">` + htmlDiagrama + `</div>`
                    )
                })
                $(".svgDiagramaSecuencia").sequenceDiagram({theme: 'simple'});
            }
        });
    </script>
</head>
<body>
<?php require_once('../header.php') ?>
<main class="container">
    <input type="hidden" value="<?php echo $tmpl_contenedor["id"] ?>" id="hidIdContenedor" />
    <input type="hidden" value="<?php echo $tipo_contenedor ?>" id="hidTipoContenedor" />
    <?php require_once $SERVER_PATH . $TEMPLATES_REL_PATH . 'maquetado/menu.tmpl.php' ?>
    <div class="row">
        <div class="col-sm-12">
            <?php require_once $SERVER_PATH . $TEMPLATES_REL_PATH . 'maquetado/mensajes.tmpl.php' ?>
            <h1><?php echo (isset($tmpl_contenedor["nombre"]))?$tmpl_contenedor["nombre"]:""; ?></h1>
            <div id="divSecciones">
                <?php if(isset($tmpl_contenedor["articulos"]) && count($tmpl_contenedor["articulos"]) > 0): ?>
                    <?php foreach ($tmpl_contenedor["articulos"] as $articulo): ?>
                        <section data-id="<?php echo $articulo["id"]?>">
                            <h3 class="titulo">
                                <?php echo $articulo["titulo"] ?>
                            </h3>
                            <div class="contenido">
                                <?php echo $articulo["contenido"] ?>
                                <?php if($sesion->is_active()): ?>
                                    <i class="borrar glyphicon glyphicon-trash btn-animado" title="Borrar"></i>
                                    <i class="editar glyphicon glyphicon-edit btn-animado" title="Editar"></i>
                                <?php endif; ?>
                            </div>
                        </section>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="font-style: italic">No hay información disponible</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php if (isset($tmpl_contenedor) && isset($tmpl_contenedor["cantidad_eliminados"]) && $tmpl_contenedor["cantidad_eliminados"] > 0 && $sesion->is_active()): ?>
        <div class="row deactivate-article">
            <div class="col-sm-12">
                <input type="checkbox" id="chkMostrarArticulosDesactivados" />
                <label for="chkMostrarArticulosDesactivados" >Mostrar artículos eliminados</label>
            </div>
        </div>
    <?php endif; ?>

    <?php if (isset($tmpl_contenedor) && $sesion->is_active()): ?>
        <div class="row create-article">
            <form role="form" action="guardar_articulo.php?id_contenedor=<?php echo $id_contenedor ?>" method="post">
                <input type="hidden" name="tipo" value="<?php echo $tipo_contenedor ?>" />
                <div class="form-group">
                    <label for="txtTitulo">Título:</label>
                    <input type="text" class="form-control" name="txtTitulo" id="txtTitulo">
                </div>
                <div class="form-group">
                    <label for="txtContenido">Contenido:</label>
                    <textarea class="form-control" rows="20" name="txtContenido" id="txtContenido"></textarea>
                </div>
                <button type="submit" class="btn btn-primary pull-right">Crear artículo</button>
            </form>
        </div>
    <?php endif; ?>

    <?php if (isset($tmpl_contenedor)): ?>

        <div class="row">
            <div class="col-sm-12">
                <h4>En este artículo participaron:</h4>
                <div id="divAutores">
                    <?php if(isset($tmpl_contenedor["autores"]) && count($tmpl_contenedor["autores"]) > 0): ?>
                        <ul>
                            <?php foreach ($tmpl_contenedor["autores"] as $autor): ?>
                                <li>
                                    <a href="<?php echo $WEB_PATH ?><?php echo $CTRL_REL_PATH ?>ver_autor.php?id_autor=<?php echo $autor['id'] ?>"><?php echo $autor['nombre'] ?></a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p style="font-style: italic; font-size: 12px">No hay autores registrados</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    <?php endif; ?>
</main>
<?php require_once('../footer.php') ?>
<div class="modal fade" id="modalConfirmarBorrado" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo $WEB_PATH ?>/src/desactivar_articulo.php" method="POST">
                <input type="hidden" value="<?php echo $tmpl_contenedor["id"] ?>" name="id_contenedor" id="hidIdContenedorModalBorrado" />
                <input type="hidden" name="tipo" value="<?php echo $tipo_contenedor ?>" />
                <input type="hidden" name="id_articulo" id="hidIdArticulo" />
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Confirmación de borrado</h4>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro que deseas borrar este artículo? Más tarde puedes deshacer este cambio.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">No</button>
                    <button type="submit" class="btn btn-primary">Sí</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="modalEditar" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="<?php echo $WEB_PATH ?>src/editar_articulo.php" method="POST">
                <input type="hidden" value="<?php echo $tmpl_contenedor["id"] ?>" name="id_contenedor" />
                <input type="hidden" name="tipo" value="<?php echo $tipo_contenedor ?>" />
                <input type="hidden" name="hidIdArticuloModalEditar" id="hidIdArticuloModalEditar" />
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Editar artículo</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="comment">Título:</label>
                        <input type="text" class="form-control" name="txtTituloModalEditar" id="txtTituloModalEditar">
                    </div>
                    <div class="form-group">
                        <label for="comment">Contenido:</label>
                        <textarea class="form-control" rows="20" name="txtContenidoModalEditar" id="txtContenidoModalEditar"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <a data-id-articulo="" id="btnVerHistorial" class="btn btn-default" >Ver historial</a>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
