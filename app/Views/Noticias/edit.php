<?php echo $this->extend($layout); ?>

<?php echo $this->section('contenido'); ?>

<main class="container">
    <div class="card mt-5">
        <div class="card-header bg-light">
            <h3 class="text-center">Editar noticia</h3>
        </div>
        <div class="card-body g-3 mt-3">
            <?php echo form_open_multipart('noticias/' . $noticia['id']); ?>
            <input type="hidden" name="_method" value="PUT" />

            <?php

            $idAt = [
                'type' => 'hidden',
                'name' => 'id',
                'value' => $noticia['id']
            ];

            $titulo = [
                'type' => 'text',
                'id' => 'titulo',
                'name' => 'titulo',
                'class' => 'form-control',
                'value' => set_value('titulo', $noticia['titulo']),
                'placeholder' => 'Titulo de la noticia',
            ];

            $desc = [
                'type' => 'text',
                'id' => 'desc',
                'name' => 'desc',
                'class' => 'form-control',
                'value' => set_value('desc', $noticia['descripcion']),
                'placeholder' => 'Descripción de la noticia'
            ];

            $categoriasOpcion = [];
            foreach ($categorias as $categoria) {
                $categoriasOpcion[$categoria['id']] = $categoria['nombre'];
            }

            //? aca deberia traerse el nombre de la categoria mediante inner join
            $valorCategoria = $noticia['id_categoria'];

            ?>

            <div class="mb-3">
                <?php echo form_input($idAt); ?>
            </div>

            <div class="mb-3">
                <?php echo form_label('Titulo', 'titulo', ['class' => 'form-label col-sm-2']); ?>
                <?php echo form_input($titulo); ?>
                <?php if (validation_show_error('titulo')) : ?>
                    <div class="alert alert-danger">
                        <?php echo validation_show_error('titulo'); ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <?php echo form_label('Descripción', 'desc', ['class' => 'form-label col-sm-2']); ?>
                <?php echo form_input($desc); ?>
                <?php if (validation_show_error('desc')) : ?>
                    <div class="alert alert-danger">
                        <?php echo validation_show_error('desc'); ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <?php echo form_label('Seleccione una categoria', 'categoria', ['class' => 'form-label col']); ?>
                <?php echo form_dropdown('categoria', $categoriasOpcion, $valorCategoria); ?>
                <?php if (validation_show_error('categoria')) : ?>
                    <div class="alert alert-danger">
                        <?php echo validation_show_error('categoria'); ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <?php if($noticia['imagen'] !== ''):?>
                <img src="<?= base_url('uploads/') . $noticia['imagen']; ?>" class="card-img-top" alt="Imagen de la noticia" style="width:100px; height:100px;">
                <?php endif;?>
                <label for="archivo">Selecciona una imagen(opcional)</label>
                <input type="file" name="archivo" id="archivo" accept="image/jpeg, image/png" value="<?= $noticia['imagen'] ?>">
                <?php if (validation_show_error('archivo')) : ?>
                    <div class="alert alert-danger">
                        <?php echo validation_show_error('archivo'); ?>
                    </div>
                <?php endif; ?>
            </div>


            <div class="mb-3">
                <?php echo form_submit('submit', 'Enviar para validar', ['class' => 'btn confirmacion']); ?>
            </div>

            <?php echo form_close(); ?>
        </div>
        <div class="card-footer bg-light"></div>
    </div>
</main>
<?php echo $this->endSection(); ?>