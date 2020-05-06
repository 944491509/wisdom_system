<template>
    <component :is="current" :type='stateType' :meetid="meetid" :view="type" :detail="detail"/>
</template>
<script>
import SignInfo from './sign-info.vue'
import QrInfo from './qr-info.vue'
import MeetingInfo from './meeting-info.vue'
import { MeetingMode } from '../common/enum'

export default {
    name: 'Info',
    props: {
        type: {
            type: String,
            default: null
        },
        stateType: {
            type: String,
            default: null
        },
        meetid: {
            default: null
        },
        detail: {
            type: Object,
            default: Object
        }
    },
    components: {
        SignInfo,
        QrInfo,
        MeetingInfo
    },
    computed: {
        current(){
            if(this.type === 'info'){
                return MeetingInfo
            }
            if(this.type === 'record'){
                return SignInfo
            }
            if(this.type === 'signinQr' || this.type === 'signoutQr'){
                return QrInfo
            }
            return null
        }
    }
}
</script>