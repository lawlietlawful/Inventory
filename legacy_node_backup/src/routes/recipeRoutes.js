/**
 * Recipe and BOM Routes
 */
const express = require('express');
const router = express.Router();
const recipeService = require('../services/recipeService');
const { authenticate, requirePermission } = require('../middleware/auth');

router.get('/', authenticate, async (req, res) => {
    try {
        const recipes = await recipeService.getRecipes();
        res.json({ success: true, count: recipes.length, recipes });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

router.get('/:id', authenticate, async (req, res) => {
    try {
        const recipe = await recipeService.getRecipeById(req.params.id);
        res.json({ success: true, recipe });
    } catch (err) {
        res.status(404).json({ success: false, error: err.message });
    }
});

router.post('/', authenticate, requirePermission('manage_recipes'), async (req, res) => {
    try {
        const result = await recipeService.createRecipe(req.body, req.user.employee_id);
        res.status(201).json(result);
    } catch (err) {
        res.status(400).json({ success: false, error: err.message });
    }
});

module.exports = router;
