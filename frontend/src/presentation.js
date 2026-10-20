// Textes de présentation centralisés pour leur traduction au prochain lot FR/EN.
export const presentation = {
  topbar: {
    invitation: 'Des découvertes pour chaque envie.',
    motto: 'De belles trouvailles. Tous les jours.',
    links: [{ label: 'Aide', to: { name: 'help' } }, { label: 'Contact', to: { name: 'contact' } }],
  },
  services: [
    { icon: 'truck', title: 'Livraison', text: 'Consulter les modalités', to: { name: 'delivery' } },
    { icon: 'heart', title: 'Vos coups de cœur', text: 'Retrouver vos favoris', to: { name: 'favorites' } },
    { icon: 'returns', title: 'Retours et échanges', text: 'Consulter les conditions', to: { name: 'returns-policy' } },
    { icon: 'support', title: 'Une question ?', text: 'Trouver une réponse', to: { name: 'help' } },
  ],
}
