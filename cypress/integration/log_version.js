/// <reference types="cypress" />

'use strict';

import { TestMethods } from '../support/test_methods.js';

describe('paylike plugin version log remotely', () => {
    /** Send log after full test finished. */
    it('log shop & paylike versions remotely', () => {
        TestMethods.logVersions();
    });
}); // describe