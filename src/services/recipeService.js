/**
 * Recipe and Bill of Materials (BOM) Service
 */
const db = require('../../database/pg-client');
const { logActivity } = require('../middleware/audit');

class RecipeService {
    async getRecipes() {
        const recipesRes = await db.query(
            `SELECT r.*, p.product_code, p.product_name, p.selling_price, p.current_stock
             FROM product_recipes r
             JOIN products p ON r.finished_product_id = p.id
             ORDER BY r.id ASC`
        );

        const recipes = recipesRes.rows;

        for (const recipe of recipes) {
            const itemsRes = await db.query(
                `SELECT ri.*, p.product_name AS ingredient_name, p.product_code AS ingredient_code, 
                        p.current_stock AS ingredient_stock, p.cost_price, u.unit_name, u.abbreviation AS unit
                 FROM product_recipe_items ri
                 JOIN products p ON ri.ingredient_product_id = p.id
                 JOIN units u ON ri.unit_id = u.id
                 WHERE ri.recipe_id = $1`,
                [recipe.id]
            );
            recipe.ingredients = itemsRes.rows;

            // Calculate estimated BOM cost per serving
            let bomCost = 0;
            itemsRes.rows.forEach(ing => {
                bomCost += parseFloat(ing.quantity_required) * parseFloat(ing.cost_price || 0);
            });
            recipe.estimated_cost = Math.round(bomCost * 100) / 100;
        }

        return recipes;
    }

    async getRecipeById(id) {
        const recipeRes = await db.query(
            `SELECT r.*, p.product_code, p.product_name, p.selling_price, p.current_stock
             FROM product_recipes r
             JOIN products p ON r.finished_product_id = p.id
             WHERE r.id = $1`,
            [id]
        );
        if (recipeRes.rows.length === 0) throw new Error('Recipe not found.');
        const recipe = recipeRes.rows[0];

        const itemsRes = await db.query(
            `SELECT ri.*, p.product_name AS ingredient_name, p.product_code AS ingredient_code, 
                    p.current_stock AS ingredient_stock, p.cost_price, u.unit_name, u.abbreviation AS unit
             FROM product_recipe_items ri
             JOIN products p ON ri.ingredient_product_id = p.id
             JOIN units u ON ri.unit_id = u.id
             WHERE ri.recipe_id = $1`,
            [id]
        );
        recipe.ingredients = itemsRes.rows;
        return recipe;
    }

    async createRecipe(data, employeeId = 'EMP-001') {
        const { finished_product_id, recipe_name, serving_size, items } = data;

        const client = await db.getClient();
        try {
            await client.query('BEGIN');

            const res = await client.query(
                `INSERT INTO product_recipes (
                    finished_product_id, recipe_name, serving_size, status, created_at, updated_at
                ) VALUES ($1, $2, $3, 'ACTIVE', NOW(), NOW())
                RETURNING id`,
                [finished_product_id, recipe_name, serving_size || 1.0]
            );

            const recipeId = res.rows[0].id;

            if (items && Array.isArray(items)) {
                for (const item of items) {
                    await client.query(
                        `INSERT INTO product_recipe_items (
                            recipe_id, ingredient_product_id, quantity_required, unit_id
                        ) VALUES ($1, $2, $3, $4)`,
                        [recipeId, item.ingredient_product_id, item.quantity_required, item.unit_id]
                    );
                }
            }

            // Mark product as recipe item
            await client.query(
                `UPDATE products SET is_recipe_item = TRUE, updated_at = NOW() WHERE id = $1`,
                [finished_product_id]
            );

            await client.query('COMMIT');
            await logActivity(employeeId, 'RECIPE_CREATED', 'RECIPES', `Created recipe '${recipe_name}' with ${items ? items.length : 0} ingredients`);

            return { success: true, recipe_id: recipeId };
        } catch (err) {
            await client.query('ROLLBACK');
            throw err;
        } finally {
            client.release();
        }
    }
}

module.exports = new RecipeService();
