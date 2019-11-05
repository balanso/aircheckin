<div id="ac-body">
  <div class="row"><div class="col-sm-12"><h4>Регистрация арендатора</h4></div></div><hr>
  <div class="row">
    <div class="col col-md-8">
      <form onsubmit="return false;" id="get_registration_link_form">
       <div class="form-group row">
         <div class="col-sm-12">
          <label for="input_service_agent_id">Сервисный партнёр</label>
          <select id="input_service_agent_id" name="service_agent_id" class="form-control custom-select" required>
            <option hidden>Выберите партнёра</option>
            <?foreach ($service_agents as $key => $value) {?>
              <option data-percent="<?=$value['percent']?>" value="<?=$value['id']?>"><?=$value['name']?></option>
              <?}?>
            </select>
          </div>
        </div>
        <div class="form-group row">
         <div class="col-sm-12">
           <label for="input_service_agent_percent">Процент сервисного партнёра <br><small>рассчитывается автоматически если не указан или 0.</small></label>
           <div class="input-group">
             <div class="input-group-prepend">
               <div class="input-group-text">%</div>
             </div>
             <input class="form-control" id="input_service_agent_percent" name="service_agent_percent" placeholder="" max="100" type="number" step="0.1"/>
           </div>
         </div>
       </div>
       <div class="form-group row">
         <div class="col-sm-12">
          <label for="input_marketing_agent_id">Маркетинговый партнёр</label>
          <select autofocus id="input_marketing_agent_id" name="marketing_agent_id" class="form-control custom-select" required>
            <option hidden>Выберите партнёра</option>
            <?foreach ($marketing_agents as $key => $value) {?>
              <option data-percent="<?=$value['percent']?>" value="<?=$value['id']?>">
               <?=$value['name']?>
             </option>
             <?}?>
           </select>
         </div>
       </div>
       <div class="form-group row">
         <div class="col-sm-12">
           <label for="input_marketing_agent_percent">Процент маркетингового партнёра</label>
           <div class="input-group">
             <div class="input-group-prepend">
               <div class="input-group-text">%</div>
             </div>
             <input class="form-control" required id="input_marketing_agent_percent" name="marketing_agent_percent" placeholder="0" max="100" type="number" step="0.1"/>
           </div>
         </div>
       </div>
       <div class="form-group row">
         <div class="col-sm-12">
          <label for="input_payment_method_id">Способ оплаты</label>
          <select autofocus id="input_payment_method_id" name="payment_method_id" class="form-control custom-select" required>
            <option hidden>Выберите способ оплаты</option>
            <?foreach ($payment_methods as $key => $value) {?>
              <option <?=($key == 0) ? 'selected' : ''?> value="<?=$value['id']?>">
               <?=$value['name']?>
             </option>
             <?}?>
           </select>
         </div>
       </div>
       <div class="form-group row" id="form_group_pms_order_id">
         <div class="col-sm-12">
           <label for="pms_order_id">Номер заказа в Exa</label>
           <div class="input-group">
             <div class="input-group-prepend">
               <div class="input-group-text">№</div>
             </div>
             <input class="form-control" required id="input_pms_order_id" name="pms_order_id" placeholder="0" type="number"/>
             <div class="input-group-append">
               <button class="btn btn-warning" type="button" id="get_pms_order_data">Загрузить заказ</button>
             </div>
           </div>
         </div>
       </div>

       <div id="js_toggle_fields" style="display:none;">
        <div class="form-group row" id="form_group_dates">
          <div class="col-sm-12">
            <label for="input_order_dates">Дата заезда - выезда</label>
            <input autocomplete="off" class="form-control readonly-clear" required id="input_order_dates" name="dates" placeholder="Выберите даты" type="text" readonly/>
          </div>
        </div>
        <div class="form-group row" id="form_group_apart_id">
         <div class="col-sm-12">
           <label for="input_apart_id">Апартамент</label>
           <select autofocus id="input_apart_id" name="apart_id" class="form-control custom-select" required>
             <option hidden value="">Выберите апартамент</option>
             <?foreach ($aparts as $key => $value) {
              if (isset($data['apart_id']) && $data['apart_id'] == $value['id']) {
                ?>
                <option selected data-tarif="<?=$value['price']?>" value="<?=$value['id']?>"><?=$value['name']?></option>
                <?} else {?>
                 <option data-tarif="<?=$value['price']?>" value="<?=$value['id']?>"><?=$value['name']?></option>
                 <?}}?>
               </select>
             </div>
           </div>
           <div id="form_group_other">
            <div class="form-group row">
              <div class="col-sm-12">
                <label for="input_tarif_per_day">Тариф в сутки</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <div class="input-group-text">₽</div>
                  </div>
                  <input class="form-control" required id="input_tarif" name="tarif" placeholder="0" type="number"/>
                </div>
              </div>
            </div>

            <div class="form-group row">
              <div class="col-sm-12">
                <label for="input_tarif_total">Итого по тарифу</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <div class="input-group-text">₽</div>
                  </div>
                  <input class="form-control" required id="input_tarif_total" name="tarif_total" type="number"/>
                </div>
              </div>
            </div>
            <div class="form-group row">
              <div class="col-sm-12">
                <label for="input_deposit">Депозит</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <div class="input-group-text">₽</div>
                  </div>
                  <input class="form-control" required id="input_deposit" name="deposit" type="number"/>
                </div>
              </div>
            </div>
            <div class="form-group row">
              <div class="col-sm-12">
                <label for="input_guests">Количество гостей</label>
                <input class="form-control" required id="input_guests" name="guests" placeholder="" type="number" min="1" value="1"/>
              </div>
            </div>
            <div class="form-group row">
              <div class="col-sm-12">
                <label for="input_cleanings">Количество уборок</label>
                <div class="input-group">
                  <input class="form-control" required id="input_cleanings" name="cleanings" value="" max="100" type="number"/>
                </div>
              </div>
            </div>

            <div class="form-group row">
              <div class="col-sm-12">
                <label for="input_cleanings">Стоимость одной уборки</label>
                <div class="input-group">
                  <input class="form-control" required id="input_cleaning_cost" name="cleaning_cost" value="" type="number"/>
                </div>
              </div>
            </div>
            <div class="form-group row">
              <div class="col-sm-12">
                <label for="input_cleanings">Телефон арендатора (опционально)</label>
                <div class="input-group">
                  <input class="form-control input_phone" id="input_phone" name="phone_view" type="tel" value="<?=$_GET['phone'] ?? ''?>"/>
                  <input id="input_phone_hidden" name="phone" type="hidden" value="<?=$_GET['phone'] ?? ''?>"/>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label for="input_comment">Дополнительные условия (опционально)</label>
              <textarea class="form-control" name="custom_conditions" id="input_custom_conditions" placeholder="Например: Информация о выходе из договора за 15 дней до даты выезда по договору, продление договора возможно только на месяц" rows="2"></textarea>
            </div>
            <input required id="input_apart_pms_id" type="hidden"/>
            <div class="form-group row">
              <div class="col-sm-12 text-center">
                <button class="btn btn-warning" id="get_registration_link">Получить ссылку на регистрацию</button>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>