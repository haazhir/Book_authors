describe('AuthorBook Website Full UI Test', () => {
  const baseUrl = 'https://authorbook.hazhir-ahmed.net';
  const authorName = `Cypress Author ${Date.now()}`;

  it('Navigates correctly via main menu items', () => {
    cy.visit(baseUrl);
    cy.contains('Authors').click();
    cy.url().should('include', '/authors.html');
    cy.contains('Books').click();
    cy.url().should('include', '/books.html');
    cy.contains('Home').click();
  });

  describe('Author CRUD Operations', () => {
    it('Adds a new author successfully', () => {
      cy.visit(`${baseUrl}/add_author.html`);
      cy.get('#name').type(authorName);
      cy.get('#bio').type('Created by Cypress test.');
      cy.get('button[type="submit"]').click();
      cy.on('window:alert', (text) => {
        expect(text.toLowerCase()).to.include('author added');
      });
      cy.url().should('include', '/authors.html');
      cy.contains(authorName).should('be.visible');
    });

    it('Edits the existing author successfully', () => {
      cy.visit(`${baseUrl}/authors.html`);
      cy.contains(authorName).parent().within(() => {
        cy.get('.edit-author').click();
      });
      const updatedBio = 'Updated bio by Cypress.';
      cy.get('#bio').clear().type(updatedBio);
      cy.get('button[type="submit"]').click();
      cy.on('window:alert', (text) => {
        expect(text.toLowerCase()).to.include('updated');
      });
      cy.url().should('include', '/authors.html');
      cy.contains(authorName).should('be.visible');
    });

    it('Deletes the existing author successfully', () => {
      cy.visit(`${baseUrl}/authors.html`);
      cy.contains(authorName).parent().within(() => {
        cy.get('.delete-author').click();
      });
      cy.on('window:confirm', () => true);
      cy.on('window:alert', (text) => {
        expect(text.toLowerCase()).to.include('deleted');
      });
      cy.contains(authorName).should('not.exist');
    });
  });

  describe('Extra UI Checks', () => {
    it('Homepage contains welcome text', () => {
      cy.visit(baseUrl);
      cy.contains('Welcome to the Library System').should('be.visible');
    });

    it('Authors page lists at least one author', () => {
      cy.visit(`${baseUrl}/authors.html`);
      cy.get('.author-item').its('length').should('be.gte', 1);
    });

    it('Books page loads without crashing', () => {
      cy.visit(`${baseUrl}/books.html`);
      cy.get('.book-list').should('exist');
    });
  });

  describe('Form Validation Checks', () => {
    it('Validates empty author form fields', () => {
      cy.visit(`${baseUrl}/add_author.html`);
      cy.get('button[type="submit"]').click();
      cy.get('#name:invalid').should('exist');
      cy.get('#bio:invalid').should('exist');
    });

    it('Validates empty book form fields', () => {
      cy.visit(`${baseUrl}/add_book.html`);
      cy.get('button[type="submit"]').click();
      cy.get('#title:invalid').should('exist');
      cy.get('#author:invalid').should('exist');
      cy.get('#year:invalid').should('exist');
    });

    it('Prevents adding duplicate authors', () => {
      cy.visit(`${baseUrl}/add_author.html`);
      cy.get('#name').type('James Clear');
      cy.get('#bio').type('Duplicate test.');
      cy.get('button[type="submit"]').click();
      cy.on('window:alert', (text) => {
        expect(text.toLowerCase()).to.include('already exists');
      });
    });
  });
});
