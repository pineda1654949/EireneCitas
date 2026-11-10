/**
 * Inicia sesion por la pantalla real de login y cachea la sesion (cookie)
 * entre pruebas con cy.session, para no repetir el login en cada escenario.
 */
Cypress.Commands.add('login', (email, password = 'contraseña') => {
    cy.session([email, password], () => {
        cy.visit('/login');
        cy.get('#email').type(email);
        cy.get('#password').type(password);
        cy.get('form').contains('button', /ingresar/i).click();
        cy.url().should('not.include', '/login');
    });
});
