<script setup>
defineProps({pagination:{type:Object,required:true}})
defineEmits(['page'])
</script>
<template><nav class="admin-pagination" :aria-label="$t('Pagination des résultats')"><p role="status">{{pagination.start}}–{{pagination.end}} / {{pagination.total}}</p><button type="button" :disabled="pagination.page<=1" @click="$emit('page',pagination.page-1)">{{ $t('Précédent') }}</button><span>{{pagination.page}} / {{pagination.totalPages}}</span><button type="button" :disabled="pagination.page>=pagination.totalPages" @click="$emit('page',pagination.page+1)">{{ $t('Suivant') }}</button></nav></template>
