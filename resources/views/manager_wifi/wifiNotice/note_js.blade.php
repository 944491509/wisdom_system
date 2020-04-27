<script language="JavaScript">
    $(document).ready(function(){
        new window.Vue({
            el: '#enrol-note-manager-app',
            data() {
                return {
                    content: '{!! !empty($dataOne['notice_content']) ? str_replace(array("\r\n", "\r", "\n"),'',$dataOne['notice_content']) : null !!}',
                    @include('reusable_elements.section.redactor_options_config',['uuid'=>$user->id])
                }
            }
        });
    });
</script>
