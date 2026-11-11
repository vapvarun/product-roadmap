import { ref, onMounted, onBeforeUnmount } from 'vue'

let uid = 0

const FOCUSABLE =
  'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]):not([tabindex="-1"]), ' +
  'select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'

/**
 * Dialog behaviour shared by every board modal.
 *
 * Give the dialog element `ref="dialogRef" role="dialog" aria-modal="true"
 * :aria-labelledby="titleId" tabindex="-1"` and its title `:id="titleId"`.
 * Moves focus into the dialog on open, keeps Tab inside it, closes on Escape,
 * locks page scroll, and returns focus to the control that opened it.
 *
 * @param {Function} close Called on Escape.
 */
export function useDialog(close) {
  const dialogRef = ref(null)
  const titleId = `roadmap-dialog-title-${++uid}`
  let opener = null
  let previousOverflow = ''

  const focusable = () =>
    Array.from(dialogRef.value ? dialogRef.value.querySelectorAll(FOCUSABLE) : []).filter(
      (el) => el.offsetParent !== null || el === document.activeElement
    )

  const onKeydown = (event) => {
    if (event.key === 'Escape') {
      event.stopPropagation()
      close()
      return
    }
    if (event.key !== 'Tab') return
    const items = focusable()
    if (!items.length) {
      event.preventDefault()
      return
    }
    const first = items[0]
    const last = items[items.length - 1]
    if (event.shiftKey && (document.activeElement === first || document.activeElement === dialogRef.value)) {
      event.preventDefault()
      last.focus()
    } else if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault()
      first.focus()
    }
  }

  onMounted(() => {
    opener = document.activeElement
    previousOverflow = document.body.style.overflow
    document.body.style.overflow = 'hidden'
    document.addEventListener('keydown', onKeydown)
    const field = dialogRef.value && dialogRef.value.querySelector(
      'input:not([type="hidden"]):not([tabindex="-1"]), textarea, select'
    )
    ;(field || dialogRef.value)?.focus()
  })

  onBeforeUnmount(() => {
    document.removeEventListener('keydown', onKeydown)
    document.body.style.overflow = previousOverflow
    if (opener && typeof opener.focus === 'function' && document.contains(opener)) opener.focus()
  })

  return { dialogRef, titleId }
}
