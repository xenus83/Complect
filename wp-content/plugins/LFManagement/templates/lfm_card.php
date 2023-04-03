
<p>
<div class="row">
    <div class="col-25">
        <label for="lfm_card__isbn" class="lfm_meta_box__label">ISBN</label>
    </div>
    <div class="col-75">
		<input type="text" name="lfm_card__isbn" class="lfm_meta_box__input"
		       id="isbn" value="<?php echo $isbn; ?>" />
    </div>
</div>
</p>
<p>
	<label for="lfm_card__year">Год издания
		<input type="number" name="lfm_card__year"
		       id="lfm_card__year" size="10" min="1900" max="2099" value="<?php echo  $year ; ?>" />
	</label>
</p>
<p>
	<label for="lfm_card__cost">Цена (₽)
		<input type="number" name="lfm_card__cost"
		       id="lfm_card__cost" size="10" min="0"  value="<?php echo  $cost ; ?>" />
	</label>
</p>
<p>
	<label for="lfm_card__title_info">Сведения к заголовку
		<input type="text" name="lfm_card__title_info"
		       id="lfm_card__title_info" size="25" min="0"  value="<?php echo  $title_info ; ?>" />
	</label>
</p>
<p>
	<label for="lfm_card__volume_p">Том
		<input type="number" name="lfm_card__volume_p"
		       id="lfm_card__volume_p" size="5"  value="<?php echo  $volume_p ; ?>" />
	</label>
</p>
<p>
	<label for="lfm_card__otv_info">Свед об отв
		<input type="text" name="lfm_card__otv_info"
		       id="lfm_card__otv_info" size="25"  value="<?php echo  $otv_info ; ?>" />
	</label>
</p>
<p>
	<label for="lfm_card__izd_info">Свед изд
		<input type="text" name="lfm_card__izd_info"
		       id="lfm_card__izd_info" size="25"  value="<?php echo  $izd_info ; ?>" />
	</label>
</p>
<p>
	<label for="lfm_card__publishing_place">Место издания
		<input type="text" name="lfm_card__publishing_place"
		       id="lfm_card__publishing_place" size="25"  value="<?php echo  $publishing_place ; ?>" />
	</label>
</p>
<p>
	<label for="lfm_card__publishing_house">Издательство
		<input type="text" name="lfm_card__publishing_house"
		       id="lfm_card__publishing_house" size="25"  value="<?php echo  $publishing_house ; ?>" />
	</label>
</p>
<p>
	<label for="lfm_card__sys">Сист. треб
		<input type="text" name="lfm_card__sys"
		       id="lfm_card__sys" size="25"  value="<?php echo  $sys ; ?>" />
	</label>
</p>
<p>
	<label for="lfm_card__osnzaglser">ОснЗаглСер
		<input type="text" name="lfm_card__osnzaglser"
		       id="lfm_card__osnzaglser" size="25"  value="<?php echo  $osnzaglser ; ?>" />
	</label>
</p>
<p>
	<label for="lfm_card__note">Примечание
		<input type="text" name="lfm_card__note"
		       id="lfm_card__note" size="25"  value="<?php echo  $note ; ?>" />
	</label>
</p>
<p>
	<label for="lfm_card__resume">Содержание
		<input type="text" name="lfm_card__resume"
		       id="lfm_card__resume" size="25"  value="<?php echo  $resume ; ?>" />
	</label>
</p>