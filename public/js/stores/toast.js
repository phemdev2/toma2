export default function registerToast() {
  Alpine.store('toast', {
    toasts: [],
    show(message, type = 'success', timeout = null) {
      const durations = { success: 3000, error: 5000, info: 4000, warning: 4000 };
      const toast = { message, type, show: true, id: Date.now() };
      this.toasts.push(toast);
      setTimeout(() => this.remove(toast), timeout ?? durations[type] ?? 3000);
    },
    remove(toast) {
      toast.show = false;
      setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== toast.id); }, 300);
    }
  });

  Alpine.data('toastStore', () => ({
    get toasts() { return Alpine.store('toast').toasts; },
    remove(toast) { Alpine.store('toast').remove(toast); }
  }));
}
