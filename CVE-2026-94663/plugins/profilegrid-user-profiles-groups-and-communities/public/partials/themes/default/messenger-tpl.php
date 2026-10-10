<?php
$pmhtmlcreator = new PM_HTML_Creator($this->profile_magic, $this->version);
$pmrequests = new PM_request;
?>
<div class="pmagic"> 
    <!-----Operationsbar Starts----->
    <div class="pm-group-view pm-dbfl pm-border-bt">
        <div class="pm-section pm-dbfl" > 
            
            <svg onclick="show_pg_section_left_panel()" class="pg-left-panel-icon" fill="#000000" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
    <path d="M15.41 16.09l-4.58-4.59 4.58-4.59L14 5.5l-6 6 6 6z"/>
    <path d="M0-.5h24v24H0z" fill="none"/>
</svg>
            
            <div class="pm-section-left-panel pm-section-nav-vertical pm-difl pm-border pm-radius5 pm-bg" id="thread_pane">
                
                <ul class="dbfl" id="threads_ul">

                </ul>
            </div>
            <form id="chat_message_form" onsubmit="pm_messenger_send_chat_message(event);">  
                <input type="text" id="receipent_field"  value="" placeholder="<?php esc_attr_e("send to..","profilegrid-user-profiles-groups-and-communities");?>" style="min-width: 550px;"/>
                <input type="hidden" id="receipent_field_rid" name="rid" value=""  />   
                <div id="message_display_area" class="pm-section-content pm-difl pm_full_width_profile"  style="min-height:400px;max-height:400px;max-width: 550px;overflow-y: scroll;">

                </div>

                <div class="pm-section-right-panel pm-section-content pm-difl pm_full_width_profile"  style="max-width: 550px;float:right;">
                    <div id="typing_on"></div>
                    <?php
                    $basic_function = new Profile_Magic_Basic_Functions( $this->profile_magic, $this->version );
                    $basic_function->pm_render_nonce_field( 'pg_send_new_message' ); 
                     ?>
                    <input type="hidden" name="action" value='pm_messenger_send_new_message' /> 
                    <input type="hidden" id="thread_hidden_field" name="tid" value=""/>
                    <textarea id="messenger_textarea" name="content" style="width:80%;height:100px;"
                              data-emoji-input="unicode" data-emojiable="true"
                               form="chat_message_form" placeholder="<?php esc_attr_e('Enter text here..','profilegrid-user-profiles-groups-and-communities');?>" onkeypress=""></textarea> 
                    <input type="hidden" name="sid" value="" />      
                    <input type="submit" name="send" value="<?php esc_attr_e("send","profilegrid-user-profiles-groups-and-communities");?>" />
                </div>
            </form>

        </div>
    </div>
</div>
