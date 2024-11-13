 <select class="form-select" name="qc_species" id="qc_species" v-model="species" v-on:change="handleSpeciesChange">
     <?php foreach ($results as $result): ?>
     <option 
    data-rip="<?= $result->yields_rip ?>" 
    data-cross_cut="<?= $result->yields_cross_cut ?>" 
    data-moulder="<?= $result->yields_moulder ?>" 
    value="<?= $result->slug ?>"><?=$result->name ?></option>
        <?php endforeach ?>
                            </select>
