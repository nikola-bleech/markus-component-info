import $ from 'jquery'

class ComponentInfo extends window.HTMLDivElement {
  constructor (self) {
    self = super(self)
    self.$ = $(self)
    self.resolveElements()
    return self
  }

  resolveElements () {
    this.$openAllLinks = $('.openAllLinks', this)
  }

  connectedCallback () {
    this.$openAllLinks.on('click', function (e) {
      e.preventDefault()
      const $el = $(e.currentTarget)
      const $links = $el.parent().next().find('ul li a')
      $links.each(function (i, e) {
        window.open($(e).attr('href'))
      })
    })
  }
}

window.customElements.define('flynt-component-info', ComponentInfo, { extends: 'div' })
